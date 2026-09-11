<?php
/**
 * Base class for a PHP-defined migration module handler.
 *
 * A module handler declares its migration items as plain "new" instances and is
 * registered with the migration manager via registerModule($handler) (typically
 * from the app's registerDBMigrationModules()). No prefix or path is required:
 * item classes are resolved by the normal Meralda class/file convention.
 *
 * Subclasses add items in the constructor via add_item(), or by overriding
 * load_items().
 */
abstract class mwmod_mw_db_migrations_moduleabs extends mw_apsubbaseobj {

	/** @var mwmod_mw_db_migrations_man */
	private $_migMan;
	/** @var mwmod_mw_db_migrations_itemabs[] */
	private $_items = [];
	/** @var bool  Whether load_items() has already run. */
	private $_loaded = false;

	final function set_migration_man($man) {
		$this->_migMan = $man;
	}

	/** @return mwmod_mw_db_migrations_man */
	final function get_migration_man() {
		return $this->_migMan;
	}

	/** Short module code used as display label and state key. */
	abstract function get_code();

	/** Optional display label (defaults to the module code). */
	function get_description() {
		return $this->get_code();
	}

	/**
	 * Declare migration items. Subclasses either add items in the constructor
	 * via add_item() or override this method.
	 */
	function load_items() {
	}

	/** Add a migration item instance. */
	final function add_item($item) {
		if ($item instanceof mwmod_mw_db_migrations_itemabs) {
			$this->_items[] = $item;
		}
		return $this;
	}

	/**
	 * Ordered list of migration items, in the order they were declared. Each
	 * item is configured with its module code.
	 *
	 * @return mwmod_mw_db_migrations_itemabs[]
	 */
	final function get_items() {
		if (!$this->_loaded) {
			$this->_loaded = true;
			$this->load_items();
		}
		foreach ($this->_items as $item) {
			$item->set_mainap($this->mainap);
			$item->set_migration_man($this->_migMan);
			$item->set_migration_module($this->get_code());
		}
		return $this->_items;
	}

}
