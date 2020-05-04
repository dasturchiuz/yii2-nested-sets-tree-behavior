<?php

namespace dasturchiuz\treebehaviorcategories;

use yii\base\Behavior;
use common\models\home\models\Organizations;

class NestedSetsTreeCategoriesBehavior extends Behavior
{
    /**
     * @var string
     */
    public $leftAttribute = 'lft';
    /**
     * @var string
     */
    public $rightAttribute = 'rgt';
    /**
     * @var string
     */
    public $depthAttribute = 'depth';
    /**
     * @var string
     */
    public $labelAttribute = 'name';
    /**
     * @var string
     */
    public $childrenOutAttribute = 'children';
    /**
     * @var string
     */
    public $labelOutAttribute = 'title';
    /**
     * @var string
     */
    public $hasChildrenOutAttribute = 'folder';
    /**
     * @var string
     */
    public $hrefOutAttribute = 'href';
    /**
     * @var null|callable
     */
    public $makeLinkCallable = null;

    public function treeCategories()
    {
        $makeNode = function ($node) {
            $newData = [
                $this->labelOutAttribute => $node['translation'][$this->labelAttribute],
                'expanded' => true,
            ];
            if (is_callable($makeLink = $this->makeLinkCallable)) {
                $newData += [
                    $this->hrefOutAttribute => $makeLink($node),
                ];
            }
            return array_merge($node, $newData);
        };

        // Trees mapped
        $trees = array();
        $collection = $this->owner->children()->with(['translation'])->asArray()->all();
        //        $collection = $this->owner->find()->orderBy(['lft' => SORT_ASC, 'tree' => SORT_DESC])->with(['translation'])->asArray()->all();

        if (count($collection) > 0) {
            foreach ($collection as &$col) $col = $makeNode($col);

            // Node Stack. Used to help building the hierarchy
            $stack = array();

            foreach ($collection as $node) {
                $item = $node;
                $item[$this->childrenOutAttribute] = array();
                $orgRecklama = [];
                if ($item['selectOrg'] != null) {
                    $d = json_decode($item['selectOrg']);
                    if (is_array($d)) {
                        foreach ($d as $it) {
                            $org = Organizations::find()->where(['id' => $it])->one();
                            $orgRecklama[] = [
                                'id' => $org->id,
                                'name' => $org->brand_name_org,
                                'img' => $org->logo_url != null ? \Yii::getAlias('@storageUrl/source') . $org->logo_url : null
                            ];
                        }
                        //$orgRecklama = exclude($item['selectOrg'], ',');
                    }
                }
                // Number of stack items
                $l = count($stack);

                // Check if we're dealing with different levels
                while ($l > 0 && $stack[$l - 1][$this->depthAttribute] >= $item[$this->depthAttribute]) {
                    array_pop($stack);
                    $l--;
                }

                // Stack is empty (we are inspecting the root)
                if ($l == 0) {
                    // Assigning the root node
                    $i = count($trees);
                    $trees[$i] = [
                        'id' =>  (int) $item['id'],
                        'depth' => (int) $item['depth'],
                        'name' => $item['translation']['name'],
                        'description' => $item['translation']['description'],
                        'selectionOrgonisation' => $orgRecklama,
                        'config' => $item['config'],
                        'cat_img' => !empty($item['cat_img']) ? \Yii::getAlias('@storageUrl/source') . $item['cat_img'] : '',
                        'status' => (int) $item['status'],
                        'tree' => (int) $item['tree'],
                        'lft' => (int) $item['lft'],
                        'rgt' => (int) $item['rgt'],

                        'children' => $item['children'],
                    ];
                    $stack[] = &$trees[$i];
                } else {
                    // Add node to parent
                    $i = count($stack[$l - 1][$this->childrenOutAttribute]);
                    $stack[$l - 1][$this->hasChildrenOutAttribute] = true;
                    $stack[$l - 1][$this->childrenOutAttribute][$i] = [
                        'id' =>  (int) $item['id'],
                        'depth' => (int) $item['depth'],
                        'name' => $item['translation']['name'],
                        'description' => $item['translation']['description'],
                        'config' => $item['config'],
                        'cat_img' => !empty($item['cat_img']) ? \Yii::getAlias('@storageUrl/source') . $item['cat_img'] : '',

                        'status' => (int) $item['status'],
                        'tree' => (int) $item['tree'],
                        'lft' => (int) $item['lft'],
                        'rgt' => (int) $item['rgt'],
                        'selectionOrgonisation' => $orgRecklama,
                        'children' => $item['children'],
                    ];
                    $stack[] = &$stack[$l - 1][$this->childrenOutAttribute][$i];
                }
            }
        }

        return $trees;
    }
}
