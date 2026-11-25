#!/usr/bin/env node
/**
 * Fails when a Twig include does not isolate its context (AGENTS.md,
 * "Always use `with_context: false` or `with only`").
 *
 * - `include(...)` calls must pass `with_context: false`.
 * - `{% include ... %}` tags must end in `only`.
 *
 * Run from the theme root: `npm run check:twig`.
 */
import { readdirSync, readFileSync } from "node:fs";
import { join } from "node:path";

const roots = ["components", "templates"];
const problems = [];

function* twigFiles(dir) {
  for (const entry of readdirSync(dir, { withFileTypes: true })) {
    const path = join(dir, entry.name);
    if (entry.isDirectory()) {
      yield* twigFiles(path);
    } else if (entry.name.endsWith(".twig")) {
      yield path;
    }
  }
}

const lineOf = (text, index) => text.slice(0, index).split("\n").length;

// Blank out Twig comments so examples in docblocks are not checked.
const withoutComments = (text) => text.replace(/\{#[\s\S]*?#\}/g, (m) => m.replace(/[^\n]/g, " "));

for (const root of roots) {
  for (const file of twigFiles(root)) {
    const text = withoutComments(readFileSync(file, "utf8"));

    // include( ... ) function calls: find the matching parenthesis.
    for (const match of text.matchAll(/(?<![\w.])include\(/g)) {
      let depth = 1;
      let i = match.index + match[0].length;
      while (i < text.length && depth > 0) {
        if (text[i] === "(") depth++;
        else if (text[i] === ")") depth--;
        i++;
      }
      const call = text.slice(match.index, i);
      if (!/with_context\s*:\s*false/.test(call)) {
        problems.push(`${file}:${lineOf(text, match.index)}: include() without with_context: false`);
      }
    }

    // {% include ... %} tags.
    for (const match of text.matchAll(/\{%-?\s*include\b[\s\S]*?-?%\}/g)) {
      if (!/\sonly\s*-?%\}$/.test(match[0])) {
        problems.push(`${file}:${lineOf(text, match.index)}: {% include %} without "only"`);
      }
    }
  }
}

if (problems.length) {
  console.error(problems.join("\n"));
  console.error(`\n${problems.length} include(s) share the parent context.`);
  process.exit(1);
}
console.log("All Twig includes isolate their context.");
