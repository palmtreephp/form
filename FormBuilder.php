<?php

namespace Palmtree\Form;

use Palmtree\NameConverter\SnakeCaseToHumanNameConverter;
use Palmtree\Form\Type\AbstractType;
use Palmtree\Form\Type\TextType;

class FormBuilder {
	protected $form;
	public static $types;

	public function __construct( $args = [] ) {
		$this->getTypeClasses();
		$this->form = new Form( $args );
	}

	public function add( $name, $type = 'text', $args = [] ) {
		$control = $this->getObject( $type, $args );

		if ( ! array_key_exists( 'name', $args ) ) {
			$control->setName( $name );
		}

		$humanName = ( new SnakeCaseToHumanNameConverter() )->normalize( $name );

		if ( $control->getLabel() === null ) {
			$control->setLabel( $humanName );
		}

		$this->form->addField( $control );

		return $this;
	}

	protected function getObject( $type = 'text', $args ) {
		/** @var AbstractType $object */
		if ( $type instanceof AbstractType ) {
			$object = $type;
		} else {
			$class = $this->getTypeClass( $type );

			if ( ! class_exists( $class ) ) {
				$class = TextType::class;
			}

			$object = new $class( $args );

			if ( $object instanceof TextType && $type !== 'text' ) {
				$object->setType( $type );
			}
		}

		return $object;
	}

	public function getTypeClass( $type ) {
		if ( array_key_exists( $type, self::$types ) ) {
			return self::$types[ $type ];
		}

		if ( class_exists( $type ) ) {
			return $type;
		}

		return null;
	}

	/**
	 * @return Form
	 */
	public function getForm() {
		return $this->form;
	}

	private function getTypeClasses() {
		if ( self::$types === null ) {
			self::$types = [];
			$namespace   = __NAMESPACE__ . '\\Type';
			$files       = glob( __DIR__ . '/Type/*Type.php' );

			if ( ! $files ) {
				return false;
			}

			foreach ( $files as $file ) {
				$class = basename( $file, '.php' );
				$type  = basename( $file, 'Type.php' );

				self::$types[ mb_strtolower( $type ) ] = $namespace . '\\' . $class;
			}
		}
	}

}
