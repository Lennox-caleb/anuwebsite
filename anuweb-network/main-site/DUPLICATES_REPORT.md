# Programme Duplicates Report

Generated 2026-09-30 08:16:45 by `tools/dedup_programmes.php` directly from a live query against `programmes` on **this** database -- not hand-typed, not carried over from an earlier session. Detected by normalised title (lowercase, punctuation stripped, "&"->"and") within the same `school_id` + `level`.

## Same-school duplicate groups (13 found)

### "Doctor of Philosophy in Business Administration and Management" — school_id 2, PhD
- **KEEP** id 32 — "Doctor of Philosophy in Business Administration and Management" (published=1, completeness score 1/9)
- REMOVE id 1 — "Doctor of Philosophy in Business Administration and Management" (published=1, completeness score 0/9) — merged into id 32, archived, old slug `phd-business-administration-and-management` redirects to `doctor-of-philosophy-in-business-administration-and-management`

### "Doctor of Philosophy in Religion" — school_id 5, PhD
- **KEEP** id 59 — "Doctor of Philosophy in Religion" (published=1, completeness score 1/9)
- REMOVE id 2 — "Doctor of Philosophy in Religion" (published=1, completeness score 0/9) — merged into id 59, archived, old slug `phd-in-religion` redirects to `doctor-of-philosophy-in-religion`

### "Master of Arts in Religion" — school_id 5, Masters
- **KEEP** id 60 — "Master of Arts in Religion" (published=1, completeness score 1/9)
- REMOVE id 9 — "Master of Arts in Religion" (published=1, completeness score 0/9) — merged into id 60, archived, old slug `ma-religion` redirects to `master-of-arts-in-religion`

### "Bachelor of Science in International Business Management" — school_id 2, Undergraduate
- **KEEP** id 35 — "Bachelor of Science in International Business Management" (published=1, completeness score 1/9)
- REMOVE id 14 — "Bachelor of Science in International Business Management" (published=1, completeness score 0/9) — merged into id 35, archived, old slug `bsc-international-business-management` redirects to `bachelor-of-science-in-international-business-management`

### "Bachelor of Science in Procurement and Supply Chain Management" — school_id 2, Undergraduate
- **KEEP** id 36 — "Bachelor of Science in Procurement and Supply Chain Management" (published=1, completeness score 1/9)
- REMOVE id 15 — "Bachelor of Science in Procurement and Supply Chain Management" (published=1, completeness score 0/9) — merged into id 36, archived, old slug `bsc-procurement-supply-chain-management` redirects to `bachelor-of-science-in-procurement-and-supply-chain-management`

### "Bachelor of Science in Computer Science" — school_id 3, Undergraduate
- **KEEP** id 43 — "Bachelor of Science in Computer Science" (published=1, completeness score 1/9)
- REMOVE id 16 — "Bachelor of Science in Computer Science" (published=1, completeness score 0/9) — merged into id 43, archived, old slug `bsc-computer-science` redirects to `bachelor-of-science-in-computer-science`

### "Bachelor of Arts in Peace and Conflict Resolution" — school_id 4, Undergraduate
- **KEEP** id 53 — "Bachelor of Arts in Peace and Conflict Resolution" (published=1, completeness score 1/9)
- REMOVE id 21 — "Bachelor of Arts in Peace and Conflict Resolution" (published=1, completeness score 0/9) — merged into id 53, archived, old slug `ba-peace-conflict-resolution` redirects to `bachelor-of-arts-in-peace-and-conflict-resolution`

### "Bachelor of Arts in Community Development" — school_id 4, Undergraduate
- **KEEP** id 54 — "Bachelor of Arts in Community Development" (published=1, completeness score 1/9)
- REMOVE id 23 — "Bachelor of Arts in Community Development" (published=1, completeness score 0/9) — merged into id 54, archived, old slug `ba-community-development` redirects to `bachelor-of-arts-in-community-development`

### "Bachelor of Arts in Christian Ministry" — school_id 5, Undergraduate
- **KEEP** id 61 — "Bachelor of Arts in Christian Ministry" (published=1, completeness score 1/9)
- REMOVE id 25 — "Bachelor of Arts in Christian Ministry" (published=1, completeness score 0/9) — merged into id 61, archived, old slug `ba-christian-ministry` redirects to `bachelor-of-arts-in-christian-ministry`

### "Diploma in Peace and Conflict Studies" — school_id 4, Diploma
- **KEEP** id 55 — "Diploma in Peace and Conflict Studies" (published=1, completeness score 1/9)
- REMOVE id 26 — "Diploma in Peace & Conflict Studies" (published=1, completeness score 0/9) — merged into id 55, archived, old slug `diploma-peace-conflict-studies` redirects to `diploma-in-peace-and-conflict-studies`

### "Diploma in Information Technology" — school_id 3, Diploma
- **KEEP** id 46 — "Diploma in Information Technology" (published=1, completeness score 1/9)
- REMOVE id 27 — "Diploma in Information Technology" (published=1, completeness score 0/9) — merged into id 46, archived, old slug `diploma-information-technology` redirects to `diploma-in-information-technology`

### "Diploma in Business Administration" — school_id 2, Diploma
- **KEEP** id 37 — "Diploma in Business Administration" (published=1, completeness score 1/9)
- REMOVE id 28 — "Diploma in Business Administration" (published=1, completeness score 0/9) — merged into id 37, archived, old slug `diploma-business-administration` redirects to `diploma-in-business-administration`

### "Certificate in Business Management" — school_id 2, Certificate
- **KEEP** id 38 — "Certificate in Business Management" (published=1, completeness score 1/9)
- REMOVE id 29 — "Certificate in Business Management" (published=1, completeness score 0/9) — merged into id 38, archived, old slug `certificate-business-management` redirects to `certificate-in-business-management`

## Cross-school title collisions (NOT merged by this tool)

- "Master of Arts in Monitoring and Evaluation" (Masters): id 5 (school_id 4), id 34 (school_id 2) — different schools, same title/level. This tool never touches these; the correct school has to come from anu.ac.ke or your own confirmation, not from data completeness.
- "Bachelor of Arts in Criminal Justice and Security Management" (Undergraduate): id 12 (school_id 4), id 31 (school_id 1) — different schools, same title/level. This tool never touches these; the correct school has to come from anu.ac.ke or your own confirmation, not from data completeness.

## Near-duplicate candidates (NOT auto-merged — same school/level, titles differ by more than punctuation, e.g. "X" vs "X (ABBREVIATION)")

This tool only auto-merges an **exact** normalised-title match, on purpose -- merging on a wording heuristic risks silently combining two different programmes. These pairs look like the same programme published under two titles, but need a human to confirm before merging:

- id 4 "Master of Business Administration" vs id 33 "Master of Business Administration (MBA)" (school_id 2, Masters)
- id 8 "Master of Science in Applied Information Technology" vs id 41 "Master of Science in Applied Information Technology (MSc. AIT)" (school_id 3, Masters)
- id 17 "Bachelor of Business and Information Technology" vs id 44 "Bachelor of Business and Information Technology (BBIT)" (school_id 3, Undergraduate)
- id 18 "Bachelor of Science in Environmental Resource Management" vs id 45 "Bachelor of Science in Environmental Resource Management (BSc. ERM)" (school_id 3, Undergraduate)

