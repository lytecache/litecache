from __future__ import annotations

import dataclasses
import sqlite3
import time

import pytest

from lytecache import LyteCache, SerializationError
from lytecache._schema import TYPE_BYTES, TYPE_FLOAT, TYPE_INT, TYPE_JSON, TYPE_STR

# -- B1 round-trip table: every row, under the default serializer="auto" ----


def test_roundtrip_bytes(cache):
    cache.set("k", b"raw bytes")
    assert cache.get("k") == b"raw bytes"


def test_roundtrip_str(cache):
    cache.set("k", "hello")
    assert cache.get("k") == "hello"


def test_roundtrip_int(cache):
    cache.set("k", 42)
    assert cache.get("k") == 42
    assert isinstance(cache.get("k"), int)


def test_roundtrip_float(cache):
    cache.set("k", 3.14)
    assert cache.get("k") == 3.14
    assert isinstance(cache.get("k"), float)


def test_roundtrip_bool(cache):
    cache.set("t", True)
    cache.set("f", False)
    assert cache.get("t") is True
    assert cache.get("f") is False


def test_roundtrip_nested_dict_and_list(cache):
    value = {"a": [1, 2, {"b": "c"}], "d": None}
    cache.set("k", value)
    assert cache.get("k") == value


def test_roundtrip_tuple_becomes_list(cache):
    # Documented behavior: tuples are JSON-encoded and always come back as lists.
    cache.set("k", (1, 2, 3))
    assert cache.get("k") == [1, 2, 3]


def test_roundtrip_plain_object_via_vars(cache):
    class Point:
        def __init__(self, x: int, y: int) -> None:
            self.x = x
            self.y = y

    cache.set("k", Point(1, 2))
    assert cache.get("k") == {"x": 1, "y": 2}


# -- dataclasses + typed retrieval via cls ----------------------------------


@dataclasses.dataclass
class Address:
    city: str
    zip_code: str


@dataclasses.dataclass
class Person:
    name: str
    age: int
    address: Address


def test_dataclass_roundtrip_as_plain_dict_without_cls(cache):
    person = Person("Ada", 30, Address("London", "E1"))
    cache.set("k", person)
    assert cache.get("k") == {
        "name": "Ada",
        "age": 30,
        "address": {"city": "London", "zip_code": "E1"},
    }


def test_dataclass_roundtrip_with_cls_reconstructs_nested_dataclass(cache):
    person = Person("Ada", 30, Address("London", "E1"))
    cache.set("k", person)
    result = cache.get("k", cls=Person)
    assert result == person
    assert isinstance(result, Person)
    assert isinstance(result.address, Address)


def test_cls_reconstruction_wrong_shape_raises_serialization_error(db_path):
    # strict=True so the failure surfaces instead of degrading to a miss.
    cache = LyteCache(db_path, sweep_interval=None, strict=True)
    try:
        cache.set("k", {"unexpected": "shape"})
        with pytest.raises(SerializationError):
            cache.get("k", cls=Person)
    finally:
        cache.close()


def test_cls_on_plain_non_dataclass_class(cache):
    class Point:
        def __init__(self, x: int, y: int) -> None:
            self.x = x
            self.y = y

    cache.set("k", {"x": 1, "y": 2})
    result = cache.get("k", cls=Point)
    assert isinstance(result, Point)
    assert (result.x, result.y) == (1, 2)


def test_cls_on_plain_class_wrong_shape_raises_serialization_error(db_path):
    class Point:
        def __init__(self, x: int, y: int) -> None:
            self.x = x
            self.y = y

    # strict=True so the failure surfaces instead of degrading to a miss.
    cache = LyteCache(db_path, sweep_interval=None, strict=True)
    try:
        cache.set("k", {"unexpected": "shape"})
        with pytest.raises(SerializationError):
            cache.get("k", cls=Point)
    finally:
        cache.close()


# -- serializer modes: auto / json / pickle --------------------------------


class NotJsonable:
    """No __dict__ and not a dataclass -- nothing for 'auto'/'json' to fall back to."""

    __slots__ = ()


def test_auto_raises_on_non_json_serializable(db_path):
    cache = LyteCache(db_path, sweep_interval=None, serializer="auto")
    try:
        with pytest.raises(SerializationError):
            cache.set("k", NotJsonable())
    finally:
        cache.close()


def test_pickle_serializer_succeeds_on_same_object(db_path):
    cache = LyteCache(db_path, sweep_interval=None, serializer="pickle")
    try:
        cache.set("k", NotJsonable())
        assert isinstance(cache.get("k"), NotJsonable)
    finally:
        cache.close()


def test_auto_with_allow_pickle_false_refuses_to_read_pickled_value(db_path):
    writer = LyteCache(db_path, sweep_interval=None, serializer="pickle")
    writer.set("k", NotJsonable())
    writer.close()

    reader = LyteCache(
        db_path, sweep_interval=None, serializer="auto", allow_pickle=False, strict=True
    )
    try:
        with pytest.raises(SerializationError):
            reader.get("k")
    finally:
        reader.close()


def test_auto_with_allow_pickle_true_reads_pickled_value(db_path):
    writer = LyteCache(db_path, sweep_interval=None, serializer="pickle")
    writer.set("k", NotJsonable())
    writer.close()

    reader = LyteCache(
        db_path, sweep_interval=None, serializer="auto", allow_pickle=True, strict=True
    )
    try:
        assert isinstance(reader.get("k"), NotJsonable)
    finally:
        reader.close()


def test_json_mode_refuses_to_read_pickled_value(db_path):
    writer = LyteCache(db_path, sweep_interval=None, serializer="pickle")
    writer.set("k", NotJsonable())
    writer.close()

    reader = LyteCache(db_path, sweep_interval=None, serializer="json", strict=True)
    try:
        with pytest.raises(SerializationError):
            reader.get("k")
    finally:
        reader.close()


def test_json_mode_rejects_allow_pickle_at_construction(tmp_path):
    with pytest.raises(ValueError):
        LyteCache(tmp_path / "x.db", serializer="json", allow_pickle=True, sweep_interval=None)


def test_invalid_serializer_raises(tmp_path):
    with pytest.raises(ValueError):
        LyteCache(tmp_path / "x.db", serializer="bogus", sweep_interval=None)


# -- cross-language safety: foreign / unknown type codes --------------------


def test_foreign_type_code_raises_serialization_error(db_path):
    cache = LyteCache(db_path, sweep_interval=None)
    cache.set("bootstrap", "x")  # ensure the schema exists
    cache.close()

    now = int(time.time() * 1000)
    blob = b"\x00\x01java-native-blob"
    conn = sqlite3.connect(str(db_path))
    conn.execute(
        "INSERT INTO cache (namespace, key, value, value_type, created_at, "
        "last_accessed, access_count, size_bytes) VALUES ('default', 'java_key', ?, 6, ?, ?, 0, ?)",
        (blob, now, now, len(blob)),
    )
    conn.commit()
    conn.close()

    reader = LyteCache(db_path, sweep_interval=None, strict=True)
    try:
        with pytest.raises(SerializationError):
            reader.get("java_key")
    finally:
        reader.close()


def test_unknown_type_code_raises_serialization_error(db_path):
    cache = LyteCache(db_path, sweep_interval=None)
    cache.set("bootstrap", "x")
    cache.close()

    now = int(time.time() * 1000)
    blob = b"whatever"
    conn = sqlite3.connect(str(db_path))
    conn.execute(
        "INSERT INTO cache (namespace, key, value, value_type, created_at, "
        "last_accessed, access_count, size_bytes) "
        "VALUES ('default', 'weird_key', ?, 99, ?, ?, 0, ?)",
        (blob, now, now, len(blob)),
    )
    conn.commit()
    conn.close()

    reader = LyteCache(db_path, sweep_interval=None, strict=True)
    try:
        with pytest.raises(SerializationError):
            reader.get("weird_key")
    finally:
        reader.close()


# -- conformance fixture: matches the Java suite's expectations exactly -----


def test_conformance_fixture_codes_0_to_4(db_path):
    """Rows built by hand per SPEC.md's encoding rules, read back typed.

    This fixture's expectations must match the companion Java test suite's:
    any change here is a change to the cross-language file format.
    """
    cache = LyteCache(db_path, sweep_interval=None)
    cache.set("bootstrap", "x")  # ensure the schema exists
    cache.close()

    now = int(time.time() * 1000)
    rows = [
        ("bytes_key", b"hello-bytes", TYPE_BYTES),
        ("str_key", b"hello-str", TYPE_STR),
        ("int_key", b"42", TYPE_INT),
        ("float_key", b"3.14", TYPE_FLOAT),
        ("json_key", b'{"a":1,"b":[1,2,3]}', TYPE_JSON),
    ]
    conn = sqlite3.connect(str(db_path))
    for key, blob, vtype in rows:
        conn.execute(
            "INSERT INTO cache (namespace, key, value, value_type, created_at, "
            "expires_at, last_accessed, access_count, size_bytes) "
            "VALUES ('default', ?, ?, ?, ?, NULL, ?, 0, ?)",
            (key, blob, vtype, now, now, len(blob)),
        )
    conn.commit()
    conn.close()

    reader = LyteCache(db_path, sweep_interval=None)
    try:
        assert reader.get("bytes_key") == b"hello-bytes"
        assert reader.get("str_key") == "hello-str"
        assert reader.get("int_key") == 42
        assert reader.get("float_key") == 3.14
        assert reader.get("json_key") == {"a": 1, "b": [1, 2, 3]}
    finally:
        reader.close()
