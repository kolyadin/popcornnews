<?php

namespace popcorn\app\controllers\editor;

use popcorn\app\controllers\EditorMain;
use popcorn\model\content\Dictionary;

class EditorDictionary extends EditorMain {

	const DictList = 'dictList';
	const DictNew  = 'dictNew';
	const DictSave = 'dictSave';
	const DictEdit = 'dictEdit';

	const DictQuickRemove = 'dictQuickRemove';

	private $dictionariesAvailable = array(
		'article' => array(
			'title'      => 'Рубрики новостей',
			'form_title' => 'Название рубрики',
			'new'        => 'Создать рубрику',
			'edit'       => 'Редактировать рубрику',
			'meta_edit'  => 'Редактирование рубрики "%s"'
		),
		'tag' => array(
			'title'      => 'Теги новостей',
			'form_title' => 'Название тега',
			'new'        => 'Создать тег',
			'edit'       => 'Редактировать тег',
			'meta_edit'  => 'Редактирование тега "%s"'
		)
	);

	private function getDictionary($entityName)
	{
		if (!in_array($entityName,array_keys($this->dictionariesAvailable))) return false;

		return new Dictionary(sprintf('pn_dictionary_news_%s',$entityName), array('id', 'name'));
	}

	public function dictQuickRemove()
	{
		$id     = (int)$this->getRequest()->get('dictId');
		$entity = $this->getRequest()->get('entity');

		$dict = $this->getDictionary($entity);

		if (!isset($id) || !$dict)
		{
			$this->ajaxReturnFail();
		}

		if ($dict->removeItem($id))
		{
			die(json_encode(array(
				'status' => 'success'
			)));
		}
	}

	public function dictEdit($entity,$id)
	{
		$dict = $this->getDictionary($entity);

		$item = $dict->getItem($id);

		$tpl = array(
			'entity' => array(
				'name'  => $entity,
				'nav'   => array($this->dictionariesAvailable[$entity]['title'], $this->dictionariesAvailable[$entity]['edit']),

			),
			'form' => array(
				'entity_title' => $this->dictionariesAvailable[$entity]['form_title'],
				'title'        => $item['name'],
				'edit'         => $item['id']
			),
			'page' => array(
				'title' => sprintf('%s | %s'
					,$this->dictionariesAvailable[$entity]['title']
					,sprintf($this->dictionariesAvailable[$entity]['meta_edit']
						,$item['name']
					)
				)
			)
		);

		$this->getTwig()->display('dict/DictForm.twig',$tpl);
	}

	public function dictList($entity)
	{
		$dict = $this->getDictionary($entity);

		$list = $dict->getList();

		$tpl = array(
			'entity' => array(
				'name'  => $entity,
				'nav'   => array($this->dictionariesAvailable[$entity]['title'], $this->dictionariesAvailable[$entity]['new'])
			),
			'dict_list' => $list,
			'page' => array(
				'title' => $this->dictionariesAvailable[$entity]['title']
			)
		);

		$this->getTwig()->display('dict/DictList.twig',$tpl);
	}

	public function dictNew($entity)
	{
		$tpl = array(
			'entity' => array(
				'name'  => $entity,
				'nav'   => array($this->dictionariesAvailable[$entity]['title'], $this->dictionariesAvailable[$entity]['new'])
			),
			'form' => array(
				'entity_title' => $this->dictionariesAvailable[$entity]['form_title']
			),
			'page' => array(
				'title' => sprintf('%s | %s'
					,$this->dictionariesAvailable[$entity]['title']
					,$this->dictionariesAvailable[$entity]['new']
				)
			)
		);

		$this->getTwig()->display('dict/DictForm.twig',$tpl);
	}

	public function dictSave($entity)
	{
		$dict = $this->getDictionary($entity);

		if ($this->getRequest()->post('edit'))
		{
			$dict->updateItem(array(
				'name' => $this->getRequest()->post('title'),
				'id'   => $this->getRequest()->post('edit')
			));
		}
		else
		{
			$dict->addItem(array('name' => $this->getRequest()->post('title')));
		}

		header(sprintf('Location:/editor/dict/%s',$entity));
		die;
	}


}