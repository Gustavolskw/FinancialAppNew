import { existsSync, readdirSync, readFileSync, statSync } from "node:fs";
import { extname, join } from "node:path";

const roots = [
  "app",
  "public",
  "react-router.config.ts",
  "vite.config.ts",
];

const allowedExtensions = new Set([".cjs", ".js", ".jsx", ".mjs", ".ts", ".tsx"]);
const ignoredDirectories = new Set([".react-router", "build", "node_modules"]);
const checks = [
  {
    name: "console statement",
    pattern: /\bconsole\.(debug|error|info|log|table|trace|warn)\s*\(/,
  },
  {
    name: "debugger statement",
    pattern: /\bdebugger\b/,
  },
  {
    name: "TypeScript suppression",
    pattern: /@ts-(expect-error|ignore|nocheck)/,
  },
  {
    name: "disabled lint rule",
    pattern: /eslint-disable/,
  },
];

const findings = [];

for (const root of roots) {
  if (!existsSync(root)) {
    continue;
  }

  scanPath(root);
}

if (findings.length > 0) {
  console.error("Frontend quality gate found code smells:");

  for (const finding of findings) {
    console.error(`- ${finding.file}:${finding.line}: ${finding.name}`);
  }

  process.exit(1);
}

console.log("Frontend quality gate passed.");

function scanPath(path) {
  const stats = statSync(path);

  if (stats.isDirectory()) {
    if (ignoredDirectories.has(path)) {
      return;
    }

    for (const child of readdirSync(path)) {
      if (ignoredDirectories.has(child)) {
        continue;
      }

      scanPath(join(path, child));
    }

    return;
  }

  if (!allowedExtensions.has(extname(path))) {
    return;
  }

  scanFile(path);
}

function scanFile(file) {
  const lines = readFileSync(file, "utf8").split(/\r?\n/);

  lines.forEach((lineContent, index) => {
    for (const check of checks) {
      if (check.pattern.test(lineContent)) {
        findings.push({
          file,
          line: index + 1,
          name: check.name,
        });
      }
    }
  });
}
