# mwmod_mw_db_migrations_man — DB Migration Manager

Multi-module DB migration manager for Meralda.

---

## How it works

- Each **module** declares its migrations either as a folder of numbered `.sql`
  files (legacy) or as a PHP **handler object** whose items are plain `new`
  instances of migration classes (new scheme).
- The manager tracks the highest applied number per module in a JSON data item
  (`state_{code}`).
- On `applyAllPending()`, it applies every migration whose number is greater than
  the stored version, in registration order. After all migrations succeed it runs
  the **views** pass.

---

## Registering modules

The app overrides `registerDBMigrationModules($man)` on the main app object.

**Legacy SQL** — pass a code + relative path:

```php
function registerDBMigrationModules($man) {
    $man->registerModule("sctrl", "modules/sctrl/db/migrations");
    $man->registerModule("myapp", "modules/myapp/db/migrations");
}
```

**PHP objects** — pass a handler instance (no prefix or path required):

```php
function registerDBMigrationModules($man) {
    $man->registerModule(new mwap_myapp_db_migrations_module());
}
```

The built-in `meralda` core module is always registered first, lazily. It is a
PHP module whose handler and items live under `modules/mw/dbcore/` (separate
from the migration engine in `modules/mw/db/migrations/`).

---

## PHP migration objects

Preferred style for new work. A module handler extends
`mwmod_mw_db_migrations_moduleabs` and declares its items via `add_item()`. Each
item extends `mwmod_mw_db_migrations_itemabs` and implements:

- `get_description()` — short label shown in the UI.
- `check()` — return `true` if the change still needs applying, `false` otherwise.
- `apply()` — perform the change; return `["ok" => bool, "error" => ?string, "warnings" => []]`.

```php
class mwap_myapp_db_migrations_module extends mwmod_mw_db_migrations_moduleabs {
    function get_code() { return "myapp"; }
    function load_items() {
        $this->add_item(new mwap_myapp_db_migrations_m000001initial());
    }
}

class mwap_myapp_db_migrations_m000001initial extends mwmod_mw_db_migrations_itemabs {
    function get_description() { return "Create myapp_items table"; }
    function check() { return !$this->table_exists("myapp_items"); }
    function apply() {
        return $this->run_sql("CREATE TABLE IF NOT EXISTS `myapp_items` (`id` int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`))");
    }
}
```

- Items are applied **in the order they are declared** (the order of the
  `add_item()` calls). Any number in the class/file name is only a human-readable
  reference and is not used by the manager.
- Item files live under the module's `db/migrations/` folder, named
  `mNNNNNNdescription.php` (starts with a letter, no underscores in the final
  segment).
- `mwmod_mw_db_migrations_itemabs` extends `mw_apsubbaseobj` and provides helpers:
  `db()`, `run_sql()`, `query()`, `fetch_assoc()`, `table_exists()`,
  `column_exists()`, `index_exists()`.

The admin UI shows a warning banner when unapplied legacy `*.sql` migrations
remain, suggesting they be ported to PHP objects.

---

## Numbered migration files (legacy)

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
