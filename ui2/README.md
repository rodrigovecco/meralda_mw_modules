# UI2 Module

UI definitions that use `users2` module for My Account (modern JS inputs).

## Usage

```php
// Extend from ui2 instead of ui:
class mwap_demo_uiadmin_main extends mwmod_mw_ui2_def_main_admin {
    // Your custom subinterfaces...
}
```

## Hierarchy

```
mwmod_mw_ui_def_main_def
└── mwmod_mw_ui2_def_main_def (override: create_subinterface_myaccount)

mwmod_mw_ui_def_main_admin  
└── mwmod_mw_ui2_def_main_admin (override: create_subinterface_myaccount)
```

## What Changes

Only `create_subinterface_myaccount()` is overridden to return `mwmod_mw_users2_ui_myaccount` instead of `mwmod_mw_users_ui_myaccount`.
