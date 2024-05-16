<?php
class GP_UnitTest_Factory extends WP_UnitTest_Factory {

	/**
	 * @var GP_UnitTest_Factory_For_Project
	 */
	public $project;

	/**
	 * @var GP_UnitTest_Factory_For_Original
	 */
	public $original;

	/**
	 * @var GP_UnitTest_Factory_For_Translation_Set
	 */
	public $translation_set;

	/**
	 * @var GP_UnitTest_Factory_For_Translation
	 */
	public $translation;

	/**
	 * @var GP_UnitTest_Factory_For_User
	 */
	public $user;

	/**
	 * @var GP_UnitTest_Factory_For_Locale
	 */
	public $locale;
}

class GP_UnitTest_Factory_For_Project extends GP_UnitTest_Factory_For_Thing {
	/**
	 * @param array $args
	 * @param array $generation_definitions
	 *
	 * @return \GP_Project
	 */
	function create( $args = [], $generation_definitions = null ) {
	}
}

class GP_UnitTest_Factory_For_User extends WP_UnitTest_Factory_For_User {

	/**
	 * @param array $args
	 *
	 * @return int
	 */
	function create_admin( $args = [] ) {
	}
}


class GP_UnitTest_Factory_For_Translation_Set extends GP_UnitTest_Factory_For_Thing {

	/**
	 * @param array $args
	 * @param array $project_args
	 *
	 * @return \GP_Translation_Set
	 */
	function create_with_project( $args = [], $project_args = [] ) {
	}

	/**
	 * @param array $args
	 * @param array $project_args
	 *
	 * @return \GP_Translation_Set
	 */
	function create_with_project_and_locale( $args = [], $project_args = [] ) {
	}
}

class GP_UnitTest_Factory_For_Original extends GP_UnitTest_Factory_For_Thing {
	/**
	 * @param array $args
	 * @param array $generation_definitions
	 *
	 * @return \GP_Original
	 */
	function create( $args = [], $generation_definitions = null ) {
	}
}

class GP_UnitTest_Factory_For_Translation extends GP_UnitTest_Factory_For_Thing {
	/**
	 * @param GP_Translation_Set $set
	 * @param array $args
	 *
	 * @return \GP_Translation
	 */
	function create_with_original_for_translation_set( $set, $args = [] ) {
	}
}

class GP_UnitTest_Factory_For_Locale extends GP_UnitTest_Factory_For_Thing {

	/**
	 * @param GP_Translation_Set $set
	 * @param array $generation_definitions
	 *
	 * @return \GP_Locale
	 */
	function create( $args = [], $generation_definitions = null ) {
	}
}

class GP_UnitTest_Factory_For_Glossary extends GP_UnitTest_Factory_For_Thing {
	/**
	 * @param array $args
	 * @param array $project_args
	 *
	 * @return \GP_Glossary
	 */
	function create_with_project_set_and_locale( $args = [], $project_args = [] ) {
	}
}


class GP_UnitTest_Factory_For_Thing {

	function create( $args = [], $generation_definitions = null ) {
	}
}
