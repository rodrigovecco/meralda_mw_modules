<?php
/**
 * Base abstract class for users2 UI components.
 *
 * The modern JS bootstrap (loadModernInputsJs) and the form renderer
 * (renderFormToContainer) used to live here. They have been promoted to
 * mwmod_mw_ui_sub_withfrm so both `users` and `users2` modules can share
 * them. This class is kept as a thin marker for users2 UI components.
 */
abstract class mwmod_mw_users2_ui_abs extends mwmod_mw_ui_sub_withfrm {
}
?>
