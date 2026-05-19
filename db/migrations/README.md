# mwmod_mw_db_migrations_man — DB Migration Manager

Multi-module DB migration manager for Meralda.

---

## How it works

- Each **module** registers a folder of numbered `.sql` files (e.g. `000001_…sql`).
- The manager tracks the highest applied number per module in a JSON data item
  (`state_{code}`).
- On `applyAllPending()`, it applies every file whose number is greater than the
  stored version, in registration order. After all migrations succeed it runs
  the **views** pass.

---

## Registering modules

The app overrides `registerDBMigrationModules($man)` on the main app object:

```php
function registerDBMigrationModules($man) {
    $man->registerModule("sctrl", "modules/sctrl/db/migrations");
    $man->registerModule("myapp", "modules/myapp/db/migrations");
}
```

The built-in `meralda` module (`modules/mw/db/migrations`) is always registered first.

---

## Numbered migration files

**Naming:** `NNNNNN_description.sql` — zero-padded integer prefix.

```
000001_initial_tables.sql
000002_updates.sql
000003_views.sql   ← avoid; use views/ subfolder instead (see below)
```

Rules:
- Never modify a file once it has been applied to any environment.
- Do not use `ALTER TABLE … ADD COLUMN … AFTER x` if column `x` may not exist on
  all instances. Either omit `AFTER` or add a guard statement before it (the runner
  skips errno 1060 — duplicate column — automatically).
- Semicolons inside SQL `COMMENT` strings are not supported by the parser; use
  em-dash (`—`) or parentheses instead.

---

## Views subfolder  (`views/`)

Place `CREATE OR REPLACE VIEW` files inside a `views/` subfolder of any module's
migrations directory:

```
modules/sctrl/db/migrations/
    000001_initial_tables.sql
    000002_updates.sql
    views/
        main.sql
        reporting.sql
```

- All files in `views/` are **re-applied on every run**, after all numbered migrations
  complete successfully.
- Files are applied in alphabetical order.
- Declare a version in the file header for human tracking (not stored in DB):
  ```sql
  -- @version 5
  CREATE OR REPLACE VIEW v_diligences AS …;
  ```
- Errors inside view files are **non-fatal**: collected as warnings, execution
  continues to the next file.

---

## Skippable errors

The runner skips the following MySQL/MariaDB errors instead of aborting, to tolerate
schema changes that were already applied manually on an instance:

| errno | Meaning |
|-------|---------|
| 1050  | Table already exists |
| 1060  | Duplicate column name (`ADD COLUMN`) |
| 1061  | Duplicate key name (`CREATE INDEX`) |
| 1091  | Can't DROP — column/key does not exist |

All other errors abort the current migration and halt the run.

---

## `applyAllPending()` return value

```php
[
  "applied" => string[],   // e.g. "[sctrl] 2 — updates"
  "errors"  => string[],   // first fatal error (empty on success)
  "views"   => [
    "applied" => string[], // e.g. "[sctrl] views/main.sql (v5)"
    "errors"  => string[], // non-fatal view errors
  ] | null,                // null if migrations failed before views ran
]
```

---

## Legacy state key migration

Call `$man->migrateLegacyStateKey()` once on app init to migrate from the old
single-module `state` JSON key to the per-module `state_meralda` key.
