<?php

namespace Palmtree\Form\Captcha;

abstract class AbstractCaptcha {
	public function getName() {
		return ( new \ReflectionClass( $this ) )->getShortName();
	}

	public function getSlug() {
		return strtolower( str_replace( ' ', '_', $this->getName() ) );
	}
}
