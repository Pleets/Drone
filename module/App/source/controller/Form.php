<?php

namespace App\Controller;

use Drone\Mvc\AbstractionController;
use Drone\Validator\FormValidator;
use Drone\Dom\Element\Form as HtmlForm;
Use Exception;

class Form extends AbstractionController
{
	public function validator()
	{
		# data to view
		$data = array();

		if ($this->isPost())
		{
			$data["success"] = true;

			$components = [
				"attributes" => [
					"fname" => [
						"required" => true,
						"minlength" => 3,
						"maxlength" => 10,
						#"alnumWhiteSpace" => true,
						"label" => "First name"
					],
					"lname" => [
						"required" => true,
						"minlength" => 3,
						"maxlength" => 5,
						#"alnumWhiteSpace" => true,
						"label" => "Last name"
					],
					"height" => [
						"type" => "range",
						"required" => true,
						"min" => 0.5,
						"max" => 2.5,
						"step" => 0.1,
						"label" => "Height"
					],
					"email" => [
						"type" => "email",
						"required" => true,
						"label" => "Email"
					],
					"date" => [
						"required" => true,
						"type" => "date",
						"label" => "Date"
					],
					"url" => [
						"required" => true,
						"type" => "url",
						"label" => "Website"
					]
				]
			];

			$form = new HtmlForm($components);
			$form->fill($_POST);

			try {
				$validator = new FormValidator($form);
				$validator->validate();

				if (!$validator->isValid())
				{
					$data["success"] = false;
					$data["messages"] = $validator->getMessages();
				}
			}
			catch (Exception $e)
			{
				$data["success"] = false;
				$data["message"] = $e->getMessage();
				return $data;
			}
		}

		return $data;
	}
}