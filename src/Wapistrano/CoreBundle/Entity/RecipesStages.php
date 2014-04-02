<?php

namespace Wapistrano\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RecipesStages
 *
 * @ORM\Table(name="recipes_stages")
 * @ORM\Entity
 */
class RecipesStages
{
    /**
     * @var integer
     *
     * @ORM\Column(name="recipe_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $recipeId = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="stage_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $stageId = '0';



    /**
     * Set recipeId
     *
     * @param integer $recipeId
     * @return RecipesStages
     */
    public function setRecipeId($recipeId)
    {
        $this->recipeId = $recipeId;

        return $this;
    }

    /**
     * Get recipeId
     *
     * @return integer 
     */
    public function getRecipeId()
    {
        return $this->recipeId;
    }

    /**
     * Set stageId
     *
     * @param integer $stageId
     * @return RecipesStages
     */
    public function setStageId($stageId)
    {
        $this->stageId = $stageId;

        return $this;
    }

    /**
     * Get stageId
     *
     * @return integer 
     */
    public function getStageId()
    {
        return $this->stageId;
    }
}
