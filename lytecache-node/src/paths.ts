/**
 * Resolution of the zero-config default database path.
 *
 * Layout: `<platform cache dir>/lytecache/<project-id>.db`, where the platform
 * cache dir is resolved with Node built-ins only, and `<project-id>` is a
 * short hash of the resolved current working directory -- so two different
 * projects on the same machine never collide, without any configuration.
 */

import { createHash } from "node:crypto";
import { homedir, platform } from "node:os";
import { join, resolve } from "node:path";
import { realpathSync } from "node:fs";

const ENV_VAR = "LYTECACHE_PATH";

/** Returns the OS-appropriate cache directory, using only Node built-ins. */
export function platformCacheDir(): string {
  if (platform() === "darwin") {
    return join(homedir(), "Library", "Caches");
  }
  if (platform() === "win32") {
    const localAppData = process.env.LOCALAPPDATA;
    return localAppData ? localAppData : join(homedir(), "AppData", "Local");
  }
  const xdgCacheHome = process.env.XDG_CACHE_HOME;
  return xdgCacheHome ? xdgCacheHome : join(homedir(), ".cache");
}

/**
 * Derives a project ID identifying the given working directory: the first 12
 * hex characters (6 bytes) of the SHA-256 digest of the directory's resolved
 * (symlink-free, absolute) path, UTF-8 encoded.
 *
 * This must match the Python (`hashlib.sha256(str(cwd.resolve()).encode("utf-8")).hexdigest()[:12]`)
 * and Java (`SHA-256` of `Path.toRealPath()`, first 6 bytes hex-encoded) derivations exactly, so
 * the same project directory resolves to the same cache file regardless of which language's
 * LyteCache created it.
 */
export function projectId(cwd: string): string {
  let resolved: string;
  try {
    resolved = realpathSync(cwd);
  } catch {
    resolved = resolve(cwd);
  }
  const digest = createHash("sha256").update(resolved, "utf8").digest("hex");
  return digest.slice(0, 12);
}

function expandHome(rawPath: string): string {
  if (rawPath === "~" || rawPath.startsWith("~/") || rawPath.startsWith(`~${process.platform === "win32" ? "\\" : "/"}`)) {
    return join(homedir(), rawPath.slice(2));
  }
  return rawPath;
}

/**
 * Resolves the default database path.
 *
 * Honors the `LYTECACHE_PATH` environment variable override; otherwise derives
 * a per-project path from the platform cache directory and the current
 * working directory.
 */
export function defaultPath(cwd: string = process.cwd()): string {
  const override = process.env[ENV_VAR];
  if (override) {
    // Matches the Python/Java overrides: tilde-expanded, but not otherwise resolved to an
    // absolute path -- a relative LYTECACHE_PATH stays relative, same as those implementations.
    return expandHome(override);
  }
  const base = join(platformCacheDir(), "lytecache");
  const pid = projectId(cwd);
  return join(base, `${pid}.db`);
}
