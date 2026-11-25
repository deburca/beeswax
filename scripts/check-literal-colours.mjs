#!/usr/bin/env node
/**
 * Fails when a template or component names a Tailwind palette colour
 * (`bg-green-100`, `text-red-500`, ...) or a bare black/white (`text-white`)
 * instead of a theme token (ADR-0001): those ignore dark mode.
 *
 * Allowed: black overlays with an opacity (`bg-black/60`), which sit on
 * photographs and video, and the files listed in `allowedBareColour`.
 *
 * Run from the theme root: `npm run check:colours`.
 */
import { readdirSync, readFileSync } from "node:fs";
import { join } from "node:path";

const roots = ["components", "templates"];
const extraFiles = ["src/main.css"];

// Files that draw over footage or a photograph, where white text on a dark
// scrim is deliberate and does not depend on the theme.
const allowedBareColour = ["components/video/", "components/cta/", "components/hero-billboard/"];

const palette = "red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose|slate|gray|zinc|neutral|stone";
const paletteColour = new RegExp(`\\b(?:text|bg|border|ring|outline|fill|stroke|from|to|via|divide|decoration|shadow)-(?:${palette})-\\d{2,3}\\b`);
const bareColour = /\b(?:text|bg|border|ring|outline|fill|stroke)-(?:white|black)(?![\w/-])/;

function* files(dir) {
  for (const entry of readdirSync(dir, { withFileTypes: true })) {
    const path = join(dir, entry.name);
    if (entry.isDirectory()) yield* files(path);
    else if (/\.(twig|css)$/.test(entry.name)) yield path;
  }
}

const problems = [];
for (const file of [...roots.flatMap((root) => [...files(root)]), ...extraFiles]) {
  const bareAllowed = allowedBareColour.some((prefix) => file.startsWith(prefix));
  readFileSync(file, "utf8")
    .split("\n")
    .forEach((line, index) => {
      if (paletteColour.test(line)) {
        problems.push(`${file}:${index + 1}: Tailwind palette colour, use a theme token`);
      } else if (!bareAllowed && bareColour.test(line)) {
        problems.push(`${file}:${index + 1}: bare black or white, use a theme token`);
      }
    });
}

if (problems.length) {
  console.error(problems.join("\n"));
  console.error(`\n${problems.length} literal colour(s).`);
  process.exit(1);
}
console.log("No literal colours outside the allowed overlays.");
