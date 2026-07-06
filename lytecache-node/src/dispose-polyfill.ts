/**
 * `Symbol.dispose` (TC39 explicit resource management) only landed in V8 relatively recently, so
 * it may not exist as a well-known symbol on every Node version in this package's supported range
 * (`engines.node >= 18`). Defining it ourselves when missing means `cache[Symbol.dispose]()` and
 * `lock[Symbol.dispose]()` always work, even on a runtime (or a `using` transpilation target) that
 * doesn't yet define it natively -- consumers on a new enough Node/TypeScript still get real
 * `using` sugar for free, since it resolves to the same symbol either way.
 */
// The real lib.esnext.disposable.d.ts types `Symbol.dispose` as `readonly unique symbol`, which a
// plain `Symbol.for(...)` value can't satisfy -- correctly, since only the *actual* well-known
// symbol should ever be assigned there. Define it through `unknown` and Object.defineProperty
// (rather than a direct assignment) so this deliberate runtime shim doesn't fight that typing.
const symbolCtor = Symbol as unknown as Record<string, symbol>;
if (typeof symbolCtor.dispose !== "symbol") {
  Object.defineProperty(Symbol, "dispose", {
    value: Symbol.for("Symbol.dispose"),
    configurable: true,
    writable: false,
    enumerable: false,
  });
}
