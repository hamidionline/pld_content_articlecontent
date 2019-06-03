<?php defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class PlgContentArticleContent extends CMSPlugin
{
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		if ($context === 'com_finder.indexer') {
			return true;
		}

		if (strpos($article->text, '{article') === false) {
			return true;
		}

		$regex = '/{article\s(.*?)}/i';

		preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);

		if ($matches) {
			BaseDatabaseModel::addIncludePath(JPATH_ROOT . '/components/com_content/models', 'ContentModel');
			$articleModel = BaseDatabaseModel::getInstance('Article', 'ContentModel', ['ignore_request' => true]);
			$articleModel->setState('filter.published', 1);
			$articleModel->setState('params', Factory::getApplication('site')->getParams());

			foreach ($matches as $match) {
				$articleModel->setState('article.id', (int)$match[1]);
				$articleItem = $articleModel->getItem();

				$showIntro = $articleItem->params->get('show_intro', 0);
				$output = $articleItem->fulltext;
				if ($showIntro || !$output) {
					$output = $articleItem->introtext . $articleItem->fulltext;
				}

				$article->text = preg_replace("|$match[0]|", addcslashes($output, '\\$'), $article->text, 1);
			}
		}
	}
}
