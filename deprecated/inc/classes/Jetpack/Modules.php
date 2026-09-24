<?php
/**
 * Modules Jetpack Modules.
 * 
 * @package rkv-theme
 */
 
namespace RKV\Utilities\Jetpack;

use RKV\Core\Jetpack\Modules as New_Modules;

if ( class_exists( __NAMESPACE__ . '\Modules' ) ) {
	return;
}

\RKV\Utilities\deprecated_class( __NAMESPACE__, 'Modules' );


/**
 * Class to manage Jetpack modules.
 */
class Modules extends New_Modules {}
