<?php
namespace SPHERE\Application\Education\Lesson\Subject;

use SPHERE\Application\Education\Lesson\Subject\Service\Data;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblCategory;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblCategorySubject;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Service\Setup;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Lesson\Subject
 */
class Service extends Extension implements IServiceInterface
{

    /** @var null|Binding */
    private $Binding = null;
    /** @var null|Structure */
    private $Structure = null;

    /**
     * Define Database Connection
     *
     * @param Identifier $Identifier
     * @param string     $EntityPath
     * @param string     $EntityNamespace
     */
    public function __construct(Identifier $Identifier, $EntityPath, $EntityNamespace)
    {

        $this->Binding = new Binding($Identifier, $EntityPath, $EntityNamespace);
        $this->Structure = new Structure($Identifier);
    }

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->Structure))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->Binding))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @return bool|TblSubject[]
     */
    public function getSubjectAll()
    {

        return (new Data($this->Binding))->getSubjectAll();
    }

    /**
     * @return bool|TblSubject[]
     */
    public function getSubjectOrientationAll()
    {

        $tblSubjectList = array();
        $tblCategory = $this->getGroupByIdentifier('ORIENTATION');
        if ($tblCategory) {
            $tblCategory = $tblCategory->getTblCategoryAll();
            if ($tblCategory) {
                array_walk($tblCategory, function (TblCategory &$tblCategory) {

                    $tblCategory = $tblCategory->getTblSubjectAll();
                });
                array_walk_recursive($tblCategory, function ($tblSubject) use (&$tblSubjectList) {

                    $tblSubjectList[] = $tblSubject;
                });
            }
        }
        return ( empty( $tblSubjectList ) ? false : $tblSubjectList );
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblGroup
     */
    public function getGroupByIdentifier($Identifier)
    {

        return (new Data($this->Binding))->getGroupByIdentifier($Identifier);
    }

    /**
     * @return bool|TblSubject[]
     */
    public function getSubjectAdvancedAll()
    {

        $tblSubjectList = array();
        $tblCategory = $this->getGroupByIdentifier('ADVANCED');
        if ($tblCategory) {
            $tblCategory = $tblCategory->getTblCategoryAll();
            if ($tblCategory) {
                array_walk($tblCategory, function (TblCategory &$tblCategory) {

                    $tblCategory = $tblCategory->getTblSubjectAll();
                });
                array_walk_recursive($tblCategory, function ($tblSubject) use (&$tblSubjectList) {

                    $tblSubjectList[] = $tblSubject;
                });
            }
        }
        return ( empty( $tblSubjectList ) ? false : $tblSubjectList );
    }

    /**
     * @return bool|TblSubject[]
     */
    public function getSubjectProfileAll()
    {

        $tblSubjectList = array();
        $tblGroup = $this->getGroupByIdentifier('STANDARD');
        if ($tblGroup) {
            $tblCategory = $tblGroup->getTblCategoryByIdentifier('PROFILE');
            if ($tblCategory) {
                $tblSubjectAll = $tblCategory->getTblSubjectAll();
                array_walk_recursive($tblSubjectAll, function ($tblSubject) use (&$tblSubjectList) {

                    $tblSubjectList[] = $tblSubject;
                });
            }
        }
        return ( empty( $tblSubjectList ) ? false : $tblSubjectList );
    }

    /**
     * @return bool|TblSubject[]
     */
    public function getSubjectReligionAll()
    {

        $tblSubjectList = array();
        $tblGroup = $this->getGroupByIdentifier('STANDARD');
        if ($tblGroup) {
            $tblCategory = $tblGroup->getTblCategoryByIdentifier('RELIGION');
            if ($tblCategory) {
                $tblSubjectAll = $tblCategory->getTblSubjectAll();
                array_walk_recursive($tblSubjectAll, function ($tblSubject) use (&$tblSubjectList) {

                    $tblSubjectList[] = $tblSubject;
                });
            }
        }
        return ( empty( $tblSubjectList ) ? false : $tblSubjectList );
    }

    /**
     * @return bool|TblSubject[]
     */
    public function getSubjectForeignLanguageAll()
    {

        $tblSubjectList = array();
        $tblGroup = $this->getGroupByIdentifier('STANDARD');
        if ($tblGroup) {
            $tblCategory = $tblGroup->getTblCategoryByIdentifier('FOREIGNLANGUAGE');
            if ($tblCategory) {
                $tblSubjectAll = $tblCategory->getTblSubjectAll();
                array_walk_recursive($tblSubjectAll, function ($tblSubject) use (&$tblSubjectList) {

                    $tblSubjectList[] = $tblSubject;
                });
            }
        }
        return ( empty( $tblSubjectList ) ? false : $tblSubjectList );
    }

    /**
     * @return bool|TblSubject[]
     */
    public function getSubjectElectiveAll()
    {

        $tblSubjectList = array();
        $tblCategory = $this->getGroupByIdentifier('ELECTIVE');
        if ($tblCategory) {
            $tblCategory = $tblCategory->getTblCategoryAll();
            if ($tblCategory) {
                array_walk($tblCategory, function (TblCategory &$tblCategory) {

                    $tblCategory = $tblCategory->getTblSubjectAll();
                });
                array_walk_recursive($tblCategory, function ($tblSubject) use (&$tblSubjectList) {

                    $tblSubjectList[] = $tblSubject;
                });
            }
        }
        return ( empty( $tblSubjectList ) ? false : $tblSubjectList );
    }

    /**
     * int
     */
    public function countSubjectAll()
    {

        return (new Data($this->Binding))->countSubjectAll();
    }

    /**
     * @param TblCategory $tblCategory
     * @param TblSubject  $tblSubject
     *
     * @return bool
     */
    public function removeCategorySubject(TblCategory $tblCategory, TblSubject $tblSubject)
    {

        return (new Data($this->Binding))->removeCategorySubject($tblCategory, $tblSubject);
    }

    /**
     * @param TblCategory $tblCategory
     * @param TblSubject  $tblSubject
     *
     * @return TblCategorySubject
     */
    public function addCategorySubject(TblCategory $tblCategory, TblSubject $tblSubject)
    {

        return (new Data($this->Binding))->addCategorySubject($tblCategory, $tblSubject);
    }

    /**
     *
     * @param TblGroup $tblGroup
     *
     * @return bool|TblCategory[]
     */
    public function getCategoryAllByGroup(TblGroup $tblGroup)
    {

        return (new Data($this->Binding))->getCategoryAllByGroup($tblGroup);
    }

    /**
     *
     * @param TblCategory $tblCategory
     *
     * @return bool|TblSubject[]
     */
    public function getSubjectAllByCategory(TblCategory $tblCategory)
    {

        return (new Data($this->Binding))->getSubjectAllByCategory($tblCategory);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblGroup
     */
    public function getGroupById($Id)
    {

        return (new Data($this->Binding))->getGroupById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCategory
     */
    public function getCategoryById($Id)
    {

        return (new Data($this->Binding))->getCategoryById($Id);
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblCategory
     */
    public function getCategoryByIdentifier($Identifier)
    {

        return (new Data($this->Binding))->getCategoryByIdentifier($Identifier);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblSubject
     */
    public function getSubjectById($Id)
    {

        return (new Data($this->Binding))->getSubjectById($Id);
    }

    /**
     * @return bool|TblGroup[]
     */
    public function getGroupAll()
    {

        return (new Data($this->Binding))->getGroupAll();
    }

    /**
     * @param IFormInterface $Form
     * @param null|array     $Subject
     *
     * @return IFormInterface|string
     */
    public function createSubject(
        IFormInterface $Form,
        $Subject
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Subject) {
            return $Form;
        }

        $Error = false;

        if (isset( $Subject['Acronym'] ) && empty( $Subject['Acronym'] )) {
            $Form->setError('Subject[Acronym]', 'Bitte geben Sie ein eineindeutiges Kürzel an');
            $Error = true;
        } else {
            if ($this->getSubjectByAcronym($Subject['Acronym'])) {
                $Form->setError('Subject[Acronym]', 'Dieses Kürzel wird bereits verwendet');
                $Error = true;
            }
        }

        if (isset( $Subject['Name'] ) && empty( $Subject['Name'] )) {
            $Form->setError('Subject[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }

        if (!$Error) {

            if ((new Data($this->Binding))->createSubject(
                $Subject['Acronym'], $Subject['Name'], $Subject['Description']
            )
            ) {
                return new Success('Das Fach wurde erfolgreich hinzugefügt')
                .new Redirect($this->getRequest()->getUrl(), 3);
            } else {
                return new Danger('Das Fach konnte nicht hinzugefügt werden')
                .new Redirect($this->getRequest()->getUrl());
            }
        }
        return $Form;
    }

    /**
     * @param string $Acronym
     *
     * @return bool|TblSubject
     */
    public function getSubjectByAcronym($Acronym)
    {

        return (new Data($this->Binding))->getSubjectByAcronym($Acronym);
    }

    /**
     * @return bool|TblSubject[]
     */
    public function getSubjectAllHavingNoCategory()
    {

        return (new Data($this->Binding))->getSubjectAllHavingNoCategory();
    }

    /**
     * @return bool|TblCategory[]
     */
    public function getCategoryAllHavingNoGroup()
    {

        return (new Data($this->Binding))->getCategoryAllHavingNoGroup();
    }
}
