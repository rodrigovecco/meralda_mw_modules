# UI2 Module

UI definitions that use `users2` module for My Account (modern JS inputs) and provide modern login/password reset interfaces with floating labels.

> **Important:** UI2 is **opt-in**. The legacy `mw/ui` module (`mwmod_mw_ui_def_main_def` / `_admin`) does NOT extend UI2 — it remains on the classic `sbadmin` template. Each app chooses which stack to use by picking its parent class.

## Usage for new apps (recommended)

New apps should extend the **composed UI2 admin class**, which already wires up all default subinterfaces (welcome, system, uidebug, users, myaccount, cfg), the side/top menus and the security-enforcement layer:

```php
// Composed UI2 admin — ready to use, just add your own subinterfaces
class mwap_myapp_uiadmin_main extends mwmod_mw_ui2_def_main_admin {
    function __construct($ap){
        parent::__construct($ap);
        // Optional: customize side menu codes
        // $this->su_cods_for_side = "users,cfg,uidebug,system,myown";
    }

    function create_subinterface_myown(){
        return new mwap_myapp_ui_myown("myown", $this);
    }
}
```

If you only want the UI2 base (no default subinterfaces / menus), extend `mwmod_mw_ui2_main` directly.

## Features

- **Modern Login UI**: Uses `mw_ui_login2.js` with floating labels and modern validation
- **Password Reset**: Modern UI for password reset request and change
- **Security Enforcement**: Forces security actions (password change, 2FA) when required
- **users2 Integration**: Uses `mwmod_mw_users2_ui_myaccount` for My Account

## Hierarchy

```
mwmod_mw_uitemplates_sbadmin_main
└── mwmod_mw_ui2_main                  (base + security enforcement + ui2 login/myaccount)
    └── mwmod_mw_ui2_def_main_def      (default subinterfaces + menus)
        └── mwmod_mw_ui2_def_main_admin (composed admin — recommended entry point)
```

The legacy `mw/ui` stack lives in parallel:

```
mwmod_mw_uitemplates_sbadmin_main
└── mwmod_mw_ui_def_main_def           (legacy default subinterfaces + menus)
    └── mwmod_mw_ui_def_main_admin     (legacy admin)
```

## Security Enforcement

The `mwmod_mw_ui2_main` class includes a security enforcement layer that:

1. Detects if user has pending mandatory actions (forced password change, etc.)
2. Only allows subinterfaces that declare themselves compatible via `isAllowedDuringForcedSecurityAction()`
3. Forces the appropriate security subinterface otherwise

## Subinterfaces

| Code | Class | Description |
|------|-------|-------------|
| `login` | `mwmod_mw_ui2_sub_uilogin` | Modern login with floating labels |
| `rememberlogindata` | `mwmod_mw_ui2_sub_rememberlogindata` | Password reset/change |
| `myaccount` | `mwmod_mw_users2_ui_myaccount_myaccount` | Modern My Account |
| `forcechangepass` | `mwmod_mw_users2_ui_forcechangepass` | Forced password change |

## JavaScript

The login UI uses `mw_ui_login2` which extends `mw_ui_login` to work with the modern JS inputs system (`mw_datainput_item_frmonpanel`).
