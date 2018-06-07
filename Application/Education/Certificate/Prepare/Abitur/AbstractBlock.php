<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 15.03.2018
 * Time: 13:44
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Abitur;

use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Extension\Extension;

/**
 * Class AbstractBlock
 *
 * @package SPHERE\Application\Education\Certificate\Prepare\Abitur
 */
abstract class AbstractBlock extends Extension
{

    /**
     * @var TblDivision|null
     */
    protected $tblDivision = null;

    /**
     * @var TblPerson|null
     */
    protected $tblPerson = null;

    /**
     * @var TblPrepareCertificate|null
     */
    protected $tblPrepareCertificate = null;

    /**
     * @var TblPrepareStudent|null
     */
    protected $tblPrepareStudent = null;

    /**
     * @var array|false
     */
    protected $AdvancedCourses = false;

    /**
     * @var array|false
     */
    protected $BasicCourses = false;

    /**
     * @var array
     */
    protected $pointsList = array();

    protected function setPointList()
    {

        $list[-1] = '';
        for ($i = 0; $i < 16; $i++) {
            $list[$i] = (string)$i;
        }

        $this->pointsList = $list;
    }

    protected function setCourses()
    {

        list($this->AdvancedCourses, $this->BasicCourses) = Prepare::useService()->getCoursesForStudent(
            $this->tblDivision,
            $this->tblPerson
        );
    }

    /**
     * @return false|TblSubject
     */
    protected function getFirstAdvancedCourse()
    {
        foreach ($this->AdvancedCourses as $tblSubject) {
            $name = $tblSubject->getName();
            if ($name == 'Deutsch' || $name == 'Mathematik') {
                return $tblSubject;
            }
        }

        return false;
    }

    /**
     * @return false|TblSubject
     */
    protected function getSecondAdvancedCourse()
    {
        foreach ($this->AdvancedCourses as $tblSubject) {
            $name = $tblSubject->getName();
            if ($name != 'Deutsch' && $name != 'Mathematik') {
                return $tblSubject;
            }
        }

        return false;
    }
}