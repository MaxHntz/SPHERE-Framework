<?php
namespace SPHERE\Application\People\Meta\Student\Service\Data;

use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Student
 *
 * @package SPHERE\Application\People\Meta\Student\Service\Data
 */
abstract class Student extends AbstractData
{

    /**
     * @param TblPerson $tblPerson
     * @param $Identifier
     * @param null $tblStudentMedicalRecord
     * @param null $tblStudentTransport
     * @param null $tblStudentBilling
     * @param null $tblStudentLocker
     * @param null $tblStudentBaptism
     * @param null $tblStudentIntegration
     * @param null $tblStudentRelease
     *
     * @return TblStudent
     */
    public function createStudent(
        TblPerson $tblPerson,
        $Identifier,
        $tblStudentMedicalRecord = null,
        $tblStudentTransport = null,
        $tblStudentBilling = null,
        $tblStudentLocker = null,
        $tblStudentBaptism = null,
        $tblStudentIntegration = null,
        $tblStudentRelease = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $this->getStudentByPerson($tblPerson);
        if (!$Entity) {

            $Entity = new TblStudent();
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setIdentifier($Identifier);
            $Entity->setTblStudentMedicalRecord($tblStudentMedicalRecord);
            $Entity->setTblStudentTransport($tblStudentTransport);
            $Entity->setTblStudentBilling($tblStudentBilling);
            $Entity->setTblStudentLocker($tblStudentLocker);
            $Entity->setTblStudentBaptism($tblStudentBaptism);
            $Entity->setTblStudentIntegration($tblStudentIntegration);
            $Entity->setTblStudentRelease($tblStudentRelease);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblStudent $tblStudent
     * @param $Identifier
     * @param null $tblStudentMedicalRecord
     * @param null $tblStudentTransport
     * @param null $tblStudentBilling
     * @param null $tblStudentLocker
     * @param null $tblStudentBaptism
     * @param null $tblStudentIntegration
     * @param null $tblStudentRelease
     *
     * @return bool
     */
    public function updateStudent(
        TblStudent $tblStudent,
        $Identifier,
        $tblStudentMedicalRecord = null,
        $tblStudentTransport = null,
        $tblStudentBilling = null,
        $tblStudentLocker = null,
        $tblStudentBaptism = null,
        $tblStudentIntegration = null,
        $tblStudentRelease = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblStudent $Entity */
        $Entity = $Manager->getEntityById('TblStudent', $tblStudent->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;

            $Entity->setIdentifier($Identifier);
            $Entity->setTblStudentMedicalRecord($tblStudentMedicalRecord);
            $Entity->setTblStudentTransport($tblStudentTransport);
            $Entity->setTblStudentBilling($tblStudentBilling);
            $Entity->setTblStudentLocker($tblStudentLocker);
            $Entity->setTblStudentBaptism($tblStudentBaptism);
            $Entity->setTblStudentIntegration($tblStudentIntegration);
            $Entity->setTblStudentRelease($tblStudentRelease);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblStudent
     */
    public function getStudentByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudent', array(
            TblStudent::SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudent
     */
    public function getStudentById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudent', $Id);
    }
}
