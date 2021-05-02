<?php

namespace Juancrrn\Lyra\Common\View;

/**
 * Modelo para las vistas
 * 
 * Cada vista debe contener las constantes VIEW_ID, VIEW_NAME y VIEW_ROUTE con
 * visibilidad pública, para que las pueda utilizar ViewManager.
 * 
 * @package lyra
 *
 * @author juancrrn
 *
 * @version 0.0.1
 */

abstract class ViewModel
{
	protected $name;
	protected $id;

	/**
	 * Procesa la lógica de la vista en el elemento <article>, que deberá 
	 * imprimir HTML y realizar lo que sea conveniente.
	 */
	abstract public function processContent(): void;

	public function getName(): string
	{
		return $this->name;
	}

	public function getId(): string
	{
		return $this->id;
	}
}