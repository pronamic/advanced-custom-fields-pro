# Third-party licences

`@wpengine/bds-react` redistributes third-party artwork. The MIT licence requires that the
original copyright notice be retained when the work is redistributed, so the
notices below ship with the package.

The Figma source file states these obligations directly in footnotes on the
Icons and Flags pages — see [`docs/icons.md`](docs/icons.md).

> **Before vendoring:** copy the exact `LICENSE` text from each upstream
> repository at the version being vendored, and replace the corresponding block
> below. The notices here record the obligation; the upstream file is
> authoritative for its wording.

**Machine-readable form.** [`licenses.json`](licenses.json) lists each section
below with the `src/` paths it covers, and [`licenses/`](licenses/) holds the
full licence texts. `bds-wporg-sources` (in `@wpengine/bds-wordpress-react`)
reads them to add the right notices when a WordPress.org plugin ships these
files. When you add, rename or re-vendor a section here, update both.
`wporg/test/unit/licenses.test.mjs` in base-wordpress fails if a heading,
text file or path drifts.

**This package's own code** is proprietary to WP Engine. Its `src/` ships in
the tarball so WordPress.org plugins can include the source of the modules
they bundle. That source is distributed under the plugin's GPL licence, for
those modules only. See
[`../base-wordpress/docs/wporg-source-distribution.md`](../base-wordpress/docs/wporg-source-distribution.md).

---

## Lucide

Used via the `lucide-react` npm package.

- Upstream: https://github.com/lucide-icons/lucide
- Licence: ISC, with MIT for the icons derived from Feather (full text in
  [`licenses/ISC-Lucide.txt`](licenses/ISC-Lucide.txt), copied from
  `lucide-react`'s own `LICENSE`)

```
ISC License

Copyright (c) 2026 Lucide Icons and Contributors

Permission to use, copy, modify, and/or distribute this software for any
purpose with or without fee is hereby granted, provided that the above
copyright notice and this permission notice appear in all copies.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.

---

The following Lucide icons are derived from the Feather project:

airplay, alert-circle, alert-octagon, alert-triangle, aperture, arrow-down-circle, arrow-down-left, arrow-down-right, arrow-down, arrow-left-circle, arrow-left, arrow-right-circle, arrow-right, arrow-up-circle, arrow-up-left, arrow-up-right, arrow-up, at-sign, calendar, cast, check, chevron-down, chevron-left, chevron-right, chevron-up, chevrons-down, chevrons-left, chevrons-right, chevrons-up, circle, clipboard, clock, code, columns, command, compass, corner-down-left, corner-down-right, corner-left-down, corner-left-up, corner-right-down, corner-right-up, corner-up-left, corner-up-right, crosshair, database, divide-circle, divide-square, dollar-sign, download, external-link, feather, frown, hash, headphones, help-circle, info, italic, key, layout, life-buoy, link-2, link, loader, lock, log-in, log-out, maximize, meh, minimize, minimize-2, minus-circle, minus-square, minus, monitor, moon, more-horizontal, more-vertical, move, music, navigation-2, navigation, octagon, pause-circle, percent, plus-circle, plus-square, plus, power, radio, rss, search, server, share, shopping-bag, sidebar, smartphone, smile, square, table-2, tablet, target, terminal, trash-2, trash, triangle, tv, type, upload, x-circle, x-octagon, x-square, x, zoom-in, zoom-out

The MIT License (MIT) (for the icons listed above)

Copyright (c) 2013-present Cole Bemis

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

## Inter

Vendored into `src/fonts/files/` (variable weight axis, normal style, all
unicode subsets) and shipped as `@wpengine/bds-react/fonts.css`.

- Upstream: https://github.com/rsms/inter
- Obtained via: `@fontsource-variable/inter`
- Licence: SIL Open Font License 1.1

```
Copyright 2016 The Inter Project Authors (https://github.com/rsms/inter)

This Font Software is licensed under the SIL Open Font License, Version 1.1.
This licence is available with a FAQ at: https://openfontlicense.org
```

The OFL permits bundling and redistribution with software. Its conditions that
apply to us: this notice must accompany the fonts, and the Reserved Font Name
must not be used for a modified version. We ship the fonts unmodified — the
vendoring script only subsets the file list and rewrites the CSS family name
from fontsource's `Inter Variable` to `Inter`; the font binaries are byte
identical to upstream.

## JetBrains Mono

Vendored into `src/fonts/files/` (weight 400, normal style, all unicode
subsets) and shipped as `@wpengine/bds-react/fonts.css`.

- Upstream: https://github.com/JetBrains/JetBrainsMono
- Obtained via: `@fontsource/jetbrains-mono`
- Licence: SIL Open Font License 1.1

```
Copyright 2020 The JetBrains Mono Project Authors
(https://github.com/JetBrains/JetBrainsMono)

This Font Software is licensed under the SIL Open Font License, Version 1.1.
This licence is available with a FAQ at: https://openfontlicense.org
```

## Circle Flags

Vendored into `src/icons/flags/`.

- Upstream: https://github.com/HatScripts/circle-flags
- Licence: MIT

```
MIT License

Copyright (c) 2020 HatScripts

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
```

---

## Brand and payment-method marks

**No licence grant.** These are not part of the default `@wpengine/bds-react` surface: the 64
`Brand/*` marks are vendored into `src/icons/brands/` and reachable only through
the explicit `@wpengine/bds-react/icons/brands/*` subpath export, so importing one is a
deliberate act. `Payment Method/*` (42 marks) is not shipped at all.

From the Figma source:

> All product names, logos, and brand marks are property of their respective
> owners and are used here for identification purposes only. Use does not imply
> endorsement. Verify licensing before using in production.

Shipping any of these marks in a product requires a specific check against that
owner's brand guidelines. Do not add one to a build without doing so.

Each mark remains the trademark of its owner; the list of vendored marks and the
Figma node each came from is in `scripts/data/brands.json`.
