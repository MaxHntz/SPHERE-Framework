<?php
namespace SPHERE\Application\Reporting\Custom\Chemnitz\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Search\Group\Group;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Custom\Chemnitz\Person
 */
class Service
{

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $TableContent = array();

        if (!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {

                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['City'] = $Item['Address'] = '';
                $Item['Denomination'] = $Item['Birthday'] = $Item['Birthplace'] = '';
                $father = null;
                $mother = null;
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    foreach ($guardianList as $guardian) {
                        if ($guardian->getServiceTblPersonFrom() && $guardian->getTblType()->getId() == 1) {
                            if (($salutation = $guardian->getServiceTblPersonFrom()->getTblSalutation())) {
                                if ($salutation->getId() == 1) {
                                    $father = $guardian->getServiceTblPersonFrom();
                                } elseif ($salutation->getId() == 2) {
                                    $mother = $guardian->getServiceTblPersonFrom();
                                }
                            } else {
                                if ($father === null) {
                                    $father = $guardian->getServiceTblPersonFrom();
                                } else {
                                    $mother = $guardian->getServiceTblPersonFrom();
                                }
                            }
                        }
                    }
                }

                if (($addressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }

                $Item['Salutation'] = $tblPerson->getSalutation();
                $Item['Father'] = $father !== null ? $father->getFirstSecondName() : '';
                $Item['Mother'] = $mother !== null ? $mother->getFirstSecondName() : '';

                if ($address !== null) {
                    $Item['StreetName'] = $address->getTblAddress()->getStreetName();
                    $Item['StreetNumber'] = $address->getTblAddress()->getStreetNumber();
                    $Item['City'] = $address->getTblAddress()->getTblCity()->getCode()
                        . ' ' . $address->getTblAddress()->getTblCity()->getName();

                    $Item['Address'] = $address->getTblAddress()->getStreetName() . ' ' .
                        $address->getTblAddress()->getStreetNumber() . ' ' .
                        $address->getTblAddress()->getTblCity()->getCode() . ' ' .
                        $address->getTblAddress()->getTblCity()->getName();
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Denomination'] = $common->getTblCommonInformation()->getDenomination();
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                    $Item['Birthplace'] = $common->getTblCommonBirthDates()->getBirthplace();
                }

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Anrede");
            $export->setValue($export->getCell("1", "0"), "Vorname Sorgeberechtigter 1");
            $export->setValue($export->getCell("2", "0"), "Vorname Sorgeberechtigter 2");
            $export->setValue($export->getCell("3", "0"), "Name");
            $export->setValue($export->getCell("4", "0"), "Konfession");
            $export->setValue($export->getCell("5", "0"), "Straße");
            $export->setValue($export->getCell("6", "0"), "Hausnr.");
            $export->setValue($export->getCell("7", "0"), "PLZ Ort");
            $export->setValue($export->getCell("8", "0"), "Schüler");
            $export->setValue($export->getCell("9", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("10", "0"), "Geburtsort");

            $Row = 1;
            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['Salutation']);
                $export->setValue($export->getCell("1", $Row), $PersonData['Father']);
                $export->setValue($export->getCell("2", $Row), $PersonData['Mother']);
                $export->setValue($export->getCell("3", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("4", $Row), $PersonData['Denomination']);
                $export->setValue($export->getCell("5", $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell("6", $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell("7", $Row), $PersonData['City']);
                $export->setValue($export->getCell("8", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("9", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("10", $Row), $PersonData['Birthplace']);

                $Row++;
            }

            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Weiblich:');
            $export->setValue($export->getCell("1", $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Männlich:');
            $export->setValue($export->getCell("1", $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Gesamt:');
            $export->setValue($export->getCell("1", $Row), count($tblPersonList));

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @return array
     */
    public function createStaffList()
    {

        $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Mitarbeiter'));
        $TableContent = array();
        if (!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {

                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getFirstSecondName();
                $Item['Birthday'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['City'] = $Item['Code'] = '';
                $Item['Address'] = '';
                $Item['Division'] = '';
                $Item['Phone1'] = $Item['Phone2'] = $Item['Mail'] = '';

                $Item['Salutation'] = $tblPerson->getSalutation();
                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                } else {

                }

                if (($addressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }
                if ($address !== null) {
                    $Item['StreetName'] = $address->getTblAddress()->getStreetName();
                    $Item['StreetNumber'] = $address->getTblAddress()->getStreetNumber();
                    $Item['Code'] = $address->getTblAddress()->getTblCity()->getCode();
                    $Item['City'] = $address->getTblAddress()->getTblCity()->getName();

                    $Item['Address'] = $address->getTblAddress()->getStreetName() . ' ' .
                        $address->getTblAddress()->getStreetNumber() . ' ' .
                        $address->getTblAddress()->getTblCity()->getCode() . ' ' .
                        $address->getTblAddress()->getTblCity()->getName();
                }

                // Todo JohK Unterbereich ermitteln über Gruppen
                $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                if ($phoneList) {
                    if (count($phoneList) > 0) {
                        $Item['Phone1'] = $phoneList[0]->getTblPhone()->getNumber();
                    }
                    if (count($phoneList) > 1) {
                        $Item['Phone2'] = $phoneList[1]->getTblPhone()->getNumber();
                    }
                }
                $mailList = Mail::useService()->getMailAllByPerson($tblPerson);
                if ($mailList) {
                    $Item['Mail'] = $mailList[0]->getTblMail()->getAddress();
                }

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createStaffListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Anrede");
            $export->setValue($export->getCell("1", "0"), "Vorname");
            $export->setValue($export->getCell("2", "0"), "Name");
            $export->setValue($export->getCell("3", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("4", "0"), "Unterbereich");
            $export->setValue($export->getCell("5", "0"), "Straße");
            $export->setValue($export->getCell("6", "0"), "Hausnr.");
            $export->setValue($export->getCell("7", "0"), "PLZ");
            $export->setValue($export->getCell("8", "0"), "Ort");
            $export->setValue($export->getCell("9", "0"), "Telefon 1");
            $export->setValue($export->getCell("10", "0"), "Telefon 2");
            $export->setValue($export->getCell("11", "0"), "Mail");

            $Row = 1;
            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['Salutation']);
                $export->setValue($export->getCell("1", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("2", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("3", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("4", $Row), $PersonData['Division']);
                $export->setValue($export->getCell("5", $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell("6", $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell("7", $Row), $PersonData['Code']);
                $export->setValue($export->getCell("8", $Row), $PersonData['City']);
                $export->setValue($export->getCell("9", $Row), $PersonData['Phone1']);
                $export->setValue($export->getCell("10", $Row), $PersonData['Phone2']);
                $export->setValue($export->getCell("11", $Row), $PersonData['Mail']);

                $Row++;
            }

            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Weiblich:');
            $export->setValue($export->getCell("1", $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Männlich:');
            $export->setValue($export->getCell("1", $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Gesamt:');
            $export->setValue($export->getCell("1", $Row), count($tblPersonList));

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @return array
     */
    public function createSchoolFeeList()
    {

        $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Schüler'));
        $TableContent = array();
        if (!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {

                $Item['DebtorNumber'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = '';
                $Item['Address'] = '';
                $Item['FatherSalutation'] = $Item['FatherLastName'] = $Item['FatherFirstName'] = $Item['Father'] = '';
                $Item['MotherSalutation'] = $Item['MotherLastName'] = $Item['MotherFirstName'] = $Item['Mother'] = '';
                $Item['Reply'] = $Item['Records'] = $Item['LastSchoolFee'] = $Item['Remarks'] = '';
                if (($debtorList = Banking::useService()->getDebtorAllByPerson($tblPerson))) {
                    $Item['DebtorNumber'] = $debtorList[0]->getDebtorNumber();
                }

                if (($addressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }

                if ($address !== null) {
                    $Item['StreetName'] = $address->getTblAddress()->getStreetName();
                    $Item['StreetNumber'] = $address->getTblAddress()->getStreetNumber();
                    $Item['Code'] = $address->getTblAddress()->getTblCity()->getCode();
                    $Item['City'] = $address->getTblAddress()->getTblCity()->getName();

                    $Item['Address'] = $address->getTblAddress()->getStreetName() . ' ' .
                        $address->getTblAddress()->getStreetNumber() . ' ' .
                        $address->getTblAddress()->getTblCity()->getCode() . ' ' .
                        $address->getTblAddress()->getTblCity()->getName();
                }

                $father = null;
                $mother = null;
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    foreach ($guardianList as $guardian) {
                        if ($guardian->getServiceTblPersonFrom() && $guardian->getTblType()->getId() == 1) {
                            if (($salutation = $guardian->getServiceTblPersonFrom()->getTblSalutation())) {
                                if ($salutation->getId() == 1) {
                                    $father = $guardian->getServiceTblPersonFrom();
                                } elseif ($salutation->getId() == 2) {
                                    $mother = $guardian->getServiceTblPersonFrom();
                                }
                            } else {
                                if ($father === null) {
                                    $father = $guardian->getServiceTblPersonFrom();
                                } else {
                                    $mother = $guardian->getServiceTblPersonFrom();
                                }
                            }
                        }
                    }
                }
                if ($father !== null) {
                    $Item['FatherSalutation'] = $father->getSalutation();
                    $Item['FatherLastName'] = $father->getLastName();
                    $Item['FatherFirstName'] = $father->getFirstSecondName();
                    $Item['Father'] = $father->getLastFirstName();
                }
                if ($mother !== null) {
                    $Item['MotherSalutation'] = $mother->getSalutation();
                    $Item['MotherLastName'] = $mother->getLastName();
                    $Item['MotherFirstName'] = $mother->getFirstSecondName();
                    $Item['Mother'] = $mother->getLastFirstName();
                }

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createSchoolFeeListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());      //ToDO Header kürzen?
            $export->setValue($export->getCell("0", "0"), "Deb.-Nr.");
            $export->setValue($export->getCell("1", "0"), "Bescheid geschickt");
            $export->setValue($export->getCell("2", "0"), "Anrede Sorgeberechtigter 1");
            $export->setValue($export->getCell("3", "0"), "Name Sorgeberechtigter 1");
            $export->setValue($export->getCell("4", "0"), "Vorname Sorgeberechtigter 1");
            $export->setValue($export->getCell("5", "0"), "Anrede Sorgeberechtigter 2");
            $export->setValue($export->getCell("6", "0"), "Name Sorgeberechtigter 2");
            $export->setValue($export->getCell("7", "0"), "Vorname Sorgeberechtigter 2");
            $export->setValue($export->getCell("8", "0"), "Unterlagen eingereicht");
            $export->setValue($export->getCell("9", "0"), "SG Vorjahr");
            $export->setValue($export->getCell("10", "0"), "1.Kind");
            $export->setValue($export->getCell("11", "0"), "GS/MS/GY");
            $export->setValue($export->getCell("12", "0"), "Klasse");
            $export->setValue($export->getCell("13", "0"), "2.Kind");
            $export->setValue($export->getCell("14", "0"), "GS/MS/GY");
            $export->setValue($export->getCell("15", "0"), "Klasse");
            $export->setValue($export->getCell("16", "0"), "3.Kind");
            $export->setValue($export->getCell("17", "0"), "GS/MS/GY");
            $export->setValue($export->getCell("18", "0"), "Klasse");
            $export->setValue($export->getCell("19", "0"), "4.Kind");
            $export->setValue($export->getCell("20", "0"), "GS/MS/GY");
            $export->setValue($export->getCell("21", "0"), "Klasse");
            $export->setValue($export->getCell("22", "0"), "Bemerkungen");
            $export->setValue($export->getCell("23", "0"), "Straße");
            $export->setValue($export->getCell("24", "0"), "Hausnummer");
            $export->setValue($export->getCell("25", "0"), "PLZ");
            $export->setValue($export->getCell("26", "0"), "Ort");

            $Row = 1;
            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['DebtorNumber']);

                $export->setValue($export->getCell("2", $Row), $PersonData['FatherSalutation']);
                $export->setValue($export->getCell("3", $Row), $PersonData['FatherLastName']);
                $export->setValue($export->getCell("4", $Row), $PersonData['FatherFirstName']);
                $export->setValue($export->getCell("5", $Row), $PersonData['MotherSalutation']);
                $export->setValue($export->getCell("6", $Row), $PersonData['MotherLastName']);
                $export->setValue($export->getCell("7", $Row), $PersonData['MotherFirstName']);

                $export->setValue($export->getCell("23", $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell("24", $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell("25", $Row), $PersonData['Code']);
                $export->setValue($export->getCell("26", $Row), $PersonData['City']);

                $Row++;
            }

            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Weiblich:');
            $export->setValue($export->getCell("1", $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Männlich:');
            $export->setValue($export->getCell("1", $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Gesamt:');
            $export->setValue($export->getCell("1", $Row), count($tblPersonList));

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createMedicList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $TableContent = array();
        if (!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {

                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['Birthday'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['City'] = $Item['Code'] = '';
                $Item['Address'] = '';

                if (($addressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                }
                if ($address !== null) {
                    $Item['StreetName'] = $address->getTblAddress()->getStreetName();
                    $Item['StreetNumber'] = $address->getTblAddress()->getStreetNumber();
                    $Item['Code'] = $address->getTblAddress()->getTblCity()->getCode();
                    $Item['City'] = $address->getTblAddress()->getTblCity()->getName();
                    $Item['Address'] = $address->getTblAddress()->getStreetName() . ' ' .
                        $address->getTblAddress()->getStreetNumber() . ' ' .
                        $address->getTblAddress()->getTblCity()->getCode() . ' ' .
                        $address->getTblAddress()->getTblCity()->getName();
                }
                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createMedicListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Name");
            $export->setValue($export->getCell("1", "0"), "Vorname");
            $export->setValue($export->getCell("2", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("3", "0"), "Straße");
            $export->setValue($export->getCell("4", "0"), "Hausnr.");
            $export->setValue($export->getCell("5", "0"), "PLZ");
            $export->setValue($export->getCell("6", "0"), "Wohnort");

            $Row = 1;
            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("1", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("2", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("3", $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell("4", $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell("5", $Row), $PersonData['Code']);
                $export->setValue($export->getCell("6", $Row), $PersonData['City']);

                $Row++;
            }

            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Weiblich:');
            $export->setValue($export->getCell("1", $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Männlich:');
            $export->setValue($export->getCell("1", $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Gesamt:');
            $export->setValue($export->getCell("1", $Row), count($tblPersonList));

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @return array
     */
    public function createInterestedPersonList()
    {

        $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Interessent'));
        $TableContent = array();
        if (!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {

                $Item['FirstName'] = $tblPerson->getFirstName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = '';
                $Item['Address'] = '';
                $Item['TypeOptionA'] = $Item['TypeOptionB'] = $Item['DivisionLevel'] = $Item['RegistrationDate'] = '';
                $Item['SchoolYear'] = '';
                $Item['Birthday'] = $Item['Birthplace'] = $Item['Denomination'] = $Item['Nationality'] = '';
                $Item['Siblings'] = '';
                $Item['Hoard'] = 'Nein';
                $Item['FatherSalutation'] = $Item['FatherLastName'] = $Item['FatherFirstName'] = $Item['Father'] = '';
                $Item['MotherSalutation'] = $Item['MotherLastName'] = $Item['MotherFirstName'] = $Item['Mother'] = '';

                if (($addressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }
                if ($address !== null) {
                    $Item['StreetName'] = $address->getTblAddress()->getStreetName();
                    $Item['StreetNumber'] = $address->getTblAddress()->getStreetNumber();
                    $Item['Code'] = $address->getTblAddress()->getTblCity()->getCode();
                    $Item['City'] = $address->getTblAddress()->getTblCity()->getName();
                    $Item['Address'] = $address->getTblAddress()->getStreetName() . ' ' .
                        $address->getTblAddress()->getStreetNumber() . ' ' .
                        $address->getTblAddress()->getTblCity()->getCode() . ' ' .
                        $address->getTblAddress()->getTblCity()->getName();
                }

                $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);
                if ($tblProspect) {
                    $tblProspectReservation = $tblProspect->getTblProspectReservation();
                    if ($tblProspectReservation) {
                        $Item['SchoolYear'] = $tblProspectReservation->getReservationYear();
                        if ($tblProspectReservation->getServiceTblTypeOptionA()) {
                            $Item['TypeOptionA'] = $tblProspectReservation->getServiceTblTypeOptionA()->getName();
                        }
                        if ($tblProspectReservation->getServiceTblTypeOptionB()) {
                            $Item['TypeOptionB'] = $tblProspectReservation->getServiceTblTypeOptionB()->getName();
                        }
                        if ($tblProspectReservation->getReservationDivision()) {
                            $Item['DivisionLevel'] = $tblProspectReservation->getReservationDivision();
                        }
                    }
                    $tblProspectAppointment = $tblProspect->getTblProspectAppointment();
                    if ($tblProspectAppointment) {
                        $Item['RegistrationDate'] = $tblProspectAppointment->getReservationDate();
                    }
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Denomination'] = $common->getTblCommonInformation()->getDenomination();
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                    $Item['Birthplace'] = $common->getTblCommonBirthDates()->getBirthplace();
                    $Item['Nationality'] = $common->getTblCommonInformation()->getNationality();
                }

                $relationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if (!empty($relationshipList)) {
                    foreach ($relationshipList as $relationship) {
                        if ($relationship->getServiceTblPersonFrom() && $relationship->getServiceTblPersonTo()
                            && $relationship->getTblType()->getName() == 'Geschwisterkind'
                        ) {
                            if ($relationship->getServiceTblPersonFrom()->getId() == $tblPerson->getId()) {
                                $Item['Siblings'] .= $relationship->getServiceTblPersonTo()->getFullName() . ' ';
                            } else {
                                $Item['Siblings'] .= $relationship->getServiceTblPersonFrom()->getFullName() . ' ';
                            }
                        }
                    }
                    $Item['Siblings'] = trim($Item['Siblings']);
                }


                $groupList = Group::useService()->getGroupAllByPerson($tblPerson);
                if (!empty($groupList)) {
                    foreach ($groupList as $group) {
                        if ($group->getName() == 'Hort') {
                            $Item['Hoard'] = 'Ja';
                        }
                    }
                }

                $father = null;
                $mother = null;
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    foreach ($guardianList as $guardian) {
                        if ($guardian->getServiceTblPersonFrom() && $guardian->getTblType()->getId() == 1) {
                            if (($salutation = $guardian->getServiceTblPersonFrom()->getTblSalutation())) {
                                if ($salutation->getId() == 1) {
                                    $father = $guardian->getServiceTblPersonFrom();
                                } elseif ($salutation->getId() == 2) {
                                    $mother = $guardian->getServiceTblPersonFrom();
                                }
                            } else {
                                if ($father === null) {
                                    $father = $guardian->getServiceTblPersonFrom();
                                } else {
                                    $mother = $guardian->getServiceTblPersonFrom();
                                }
                            }
                        }
                    }
                }
                if ($father !== null) {
                    $Item['FatherSalutation'] = $father->getSalutation();
                    $Item['FatherLastName'] = $father->getLastName();
                    $Item['FatherFirstName'] = $father->getFirstSecondName();
                    $Item['Father'] = $father->getFullName();
                }
                if ($mother !== null) {
                    $Item['MotherSalutation'] = $mother->getSalutation();
                    $Item['MotherLastName'] = $mother->getLastName();
                    $Item['MotherFirstName'] = $mother->getFirstSecondName();
                    $Item['Mother'] = $mother->getFullName();
                }

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createInterestedPersonListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Anmeldedatum");
            $export->setValue($export->getCell("1", "0"), "Vorname");
            $export->setValue($export->getCell("2", "0"), "Name");
            $export->setValue($export->getCell("3", "0"), "Schuljahr");
            $export->setValue($export->getCell("4", "0"), "Klassenstufe");
            $export->setValue($export->getCell("5", "0"), "Schulart 1");
            $export->setValue($export->getCell("6", "0"), "Schulart 2");
            $export->setValue($export->getCell("7", "0"), "Straße");
            $export->setValue($export->getCell("8", "0"), "Hausnummer");
            $export->setValue($export->getCell("9", "0"), "PLZ");
            $export->setValue($export->getCell("10", "0"), "Ort");
            $export->setValue($export->getCell("11", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("12", "0"), "Geburtsort");
            $export->setValue($export->getCell("13", "0"), "Staatsangeh.");
            $export->setValue($export->getCell("14", "0"), "Bekenntnis");
            $export->setValue($export->getCell("15", "0"), "Geschwister");
            $export->setValue($export->getCell("16", "0"), "Hort");
            $export->setValue($export->getCell("17", "0"), "Anrede Sorgeberechtigter 1");
            $export->setValue($export->getCell("18", "0"), "Name Sorgeberechtigter 1");
            $export->setValue($export->getCell("19", "0"), "Vorname Sorgeberechtigter 1");
            $export->setValue($export->getCell("20", "0"), "Anrede Sorgeberechtigter 2");
            $export->setValue($export->getCell("21", "0"), "Name Sorgeberechtigter 2");
            $export->setValue($export->getCell("22", "0"), "Vorname Sorgeberechtigter 2");

            $Row = 1;
            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['RegistrationDate']);
                $export->setValue($export->getCell("1", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("2", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("3", $Row), $PersonData['SchoolYear']);
                $export->setValue($export->getCell("4", $Row), $PersonData['DivisionLevel']);
                $export->setValue($export->getCell("5", $Row), $PersonData['TypeOptionA']);
                $export->setValue($export->getCell("6", $Row), $PersonData['TypeOptionB']);
                $export->setValue($export->getCell("7", $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell("8", $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell("9", $Row), $PersonData['Code']);
                $export->setValue($export->getCell("10", $Row), $PersonData['City']);
                $export->setValue($export->getCell("11", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("12", $Row), $PersonData['Birthplace']);
                $export->setValue($export->getCell("13", $Row), $PersonData['Nationality']);
                $export->setValue($export->getCell("14", $Row), $PersonData['Denomination']);
                $export->setValue($export->getCell("15", $Row), $PersonData['Siblings']);
                $export->setValue($export->getCell("16", $Row), $PersonData['Hoard']);
                $export->setValue($export->getCell("17", $Row), $PersonData['FatherSalutation']);
                $export->setValue($export->getCell("18", $Row), $PersonData['FatherLastName']);
                $export->setValue($export->getCell("19", $Row), $PersonData['FatherFirstName']);
                $export->setValue($export->getCell("20", $Row), $PersonData['MotherSalutation']);
                $export->setValue($export->getCell("21", $Row), $PersonData['MotherLastName']);
                $export->setValue($export->getCell("22", $Row), $PersonData['MotherFirstName']);

                $Row++;
            }

            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Weiblich:');
            $export->setValue($export->getCell("1", $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Männlich:');
            $export->setValue($export->getCell("1", $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Gesamt:');
            $export->setValue($export->getCell("1", $Row), count($tblPersonList));

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createParentTeacherConferenceList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $TableContent = array();
        if (!empty($tblPersonList)) {
//            foreach ($studentList as $tblPerson) {
//                $tblPerson->Attendance = '';
//            }
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {

                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['Attendance'] = '';

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createParentTeacherConferenceListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Name");
            $export->setValue($export->getCell("1", "0"), "Vorname");
            $export->setValue($export->getCell("2", "0"), "Anwesenheit");

            $Row = 1;
            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("1", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("2", $Row), $PersonData['Attendance']);

                $Row++;
            }

            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Weiblich:');
            $export->setValue($export->getCell("1", $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Männlich:');
            $export->setValue($export->getCell("1", $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Gesamt:');
            $export->setValue($export->getCell("1", $Row), count($tblPersonList));

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @return array
     */
    public function createClubMemberList()
    {

        $tblGroup = Group::useService()->getGroupByName('Verein');
        $TableContent = array();
        if ($tblGroup) {
            $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
            if ($tblPersonList) {
                array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {

                    $Item['FirstName'] = $tblPerson->getFirstSecondName();
                    $Item['LastName'] = $tblPerson->getLastName();
                    $Item['Salutation'] = $tblPerson->getSalutation();
                    $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = '';
                    $Item['Address'] = '';
                    $Item['Phone'] = $Item['Mail'] = '';
                    $Item['Directorate'] = '';

                    if (($addressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                        $address = $addressList[0];
                    } else {
                        $address = null;
                    }
                    if ($address !== null) {
                        $Item['StreetName'] = $address->getTblAddress()->getStreetName();
                        $Item['StreetNumber'] = $address->getTblAddress()->getStreetNumber();
                        $Item['Code'] = $address->getTblAddress()->getTblCity()->getCode();
                        $Item['City'] = $address->getTblAddress()->getTblCity()->getName();
                        $Item['Address'] = $address->getTblAddress()->getStreetName() . ' ' .
                            $address->getTblAddress()->getStreetNumber() . ' ' .
                            $address->getTblAddress()->getTblCity()->getCode() . ' ' .
                            $address->getTblAddress()->getTblCity()->getName();
                    }
                    $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                    if ($phoneList) {
                        $Item['Phone'] = $phoneList[0]->getTblPhone()->getNumber();
                    }
                    $mailList = Mail::useService()->getMailAllByPerson($tblPerson);
                    if ($mailList) {
                        $Item['Mail'] = $mailList[0]->getTblMail()->getAddress();
                    }

                    array_push($TableContent, $Item);
                });
            }

            return $TableContent;
        }

        return false;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createClubMemberListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Anrede");
            $export->setValue($export->getCell("1", "0"), "Vorname");
            $export->setValue($export->getCell("2", "0"), "Name");
            $export->setValue($export->getCell("3", "0"), "Straße");
            $export->setValue($export->getCell("4", "0"), "Hausnr.");
            $export->setValue($export->getCell("5", "0"), "PLZ");
            $export->setValue($export->getCell("6", "0"), "Ort");
            $export->setValue($export->getCell("7", "0"), "Telefon");
            $export->setValue($export->getCell("8", "0"), "Mail");
            $export->setValue($export->getCell("9", "0"), "Vorstand");

            $Row = 1;
            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['Salutation']);
                $export->setValue($export->getCell("1", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("2", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("3", $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell("4", $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell("5", $Row), $PersonData['Code']);
                $export->setValue($export->getCell("6", $Row), $PersonData['City']);
                $export->setValue($export->getCell("7", $Row), $PersonData['Phone']);
                $export->setValue($export->getCell("8", $Row), $PersonData['Mail']);
                $export->setValue($export->getCell("9", $Row), $PersonData['Directorate']);

                $Row++;
            }

            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Weiblich:');
            $export->setValue($export->getCell("1", $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Männlich:');
            $export->setValue($export->getCell("1", $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Gesamt:');
            $export->setValue($export->getCell("1", $Row), count($tblPersonList));

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createPrintClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $TableContent = array();
        if (!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {

                $Item['Orientation'] = '';
                $Item['Education'] = '';
                $Item['ExcelNameRow2'] = '';
                $Item['Address'] = '';
                $Item['ExcelAddressRow1'] = '';
                $Item['ExcelAddressRow2'] = '';
                $Item['Birthday'] = $Item['Birthplace'] = '';
                $Item['PhoneNumbers'] = '';
                $Item['Orientation'] = '';
                $Item['Education'] = '';
                $Item['Group'] = '';

                $father = null;
                $mother = null;
                $fatherPhoneList = false;
                $motherPhoneList = false;

                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    foreach ($guardianList as $guardian) {
                        if ($guardian->getTblType()->getId() == 1) {
                            if ($father === null) {
                                $father = $guardian->getServiceTblPersonFrom();
                                if ($father) {
                                    $fatherPhoneList = Phone::useService()->getPhoneAllByPerson($father);
                                }
                            } else {
                                $mother = $guardian->getServiceTblPersonFrom();
                                if ($mother) {
                                    $motherPhoneList = Phone::useService()->getPhoneAllByPerson($mother);
                                }
                            }
                        }
                    }
                }

                if (($addressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }

                $Item['FatherName'] = $father ? ($tblPerson->getLastName() == $father->getLastName()
                    ? $father->getFirstSecondName() : $father->getFirstSecondName() . ' ' . $father->getLastName()) : '';
                $Item['MotherName'] = $mother ? ($tblPerson->getLastName() == $mother->getLastName()
                    ? $mother->getFirstSecondName() : $mother->getFirstSecondName() . ' ' . $mother->getLastName()) : '';
                $Item['DisplayName'] = $tblPerson->getLastFirstName()
                    . ($father || $mother ? '<br>(' . ($father ? $Item['FatherName']
                            . ($mother ? ', ' : '') : '')
                        . ($mother ? $Item['MotherName'] : '') . ')' : '');

                $Item['ExcelNameRow1'] = $tblPerson->getLastFirstName();
                if ($father || $mother) {
                    $Item['ExcelNameRow2'] = '(' . ($father ? $Item['FatherName']
                            . ($mother ? ', ' : '') : '')
                        . ($mother ? $Item['MotherName'] : '') . ')';
                }

                if ($address !== null) {
                    $Item['Address'] = $address->getTblAddress()->getStreetName() . ' ' .
                        $address->getTblAddress()->getStreetNumber() . '<br>' .
                        $address->getTblAddress()->getTblCity()->getCode() . ' ' .
                        $address->getTblAddress()->getTblCity()->getDisplayName();
                    $Item['ExcelAddressRow1'] = $address->getTblAddress()->getStreetName() . ' ' .
                        $address->getTblAddress()->getStreetNumber();
                    $Item['ExcelAddressRow2'] = $address->getTblAddress()->getTblCity()->getCode() . ' ' .
                        $address->getTblAddress()->getTblCity()->getDisplayName();
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                    $Item['Birthplace'] = $common->getTblCommonBirthDates()->getBirthplace();
                }

                $phoneNumbers = array();
                $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                if ($phoneList) {
                    foreach ($phoneList as $phone) {
                        $phoneNumbers[] = $phone->getTblPhone()->getNumber() . ' ' . $phone->getTblType()->getName();
                        if ($phone->getRemark()) {
                            $phoneNumbers[] = $phone->getRemark();
                        }
                    }
                }
                if ($fatherPhoneList) {
                    foreach ($fatherPhoneList as $phone) {
                        if ($phone->getServiceTblPerson()) {
                            $type = $phone->getTblType()->getName() == "Geschäftlich" ? "Geschäftl." : $phone->getTblType()->getName();
                            $phoneNumbers[] = $phone->getTblPhone()->getNumber() . ' ' . $type;
                            $phoneNumbers[] = $phone->getServiceTblPerson()->getLastFirstName();
                            if ($phone->getRemark()) {
                                $phoneNumbers[] = $phone->getRemark();
                            }
                        }
                    }
                }
                if ($motherPhoneList) {
                    foreach ($motherPhoneList as $phone) {
                        if ($phone->getServiceTblPerson()) {
                            $type = $phone->getTblType()->getName() == "Geschäftlich" ? "Geschäftl." : $phone->getTblType()->getName();
                            $phoneNumbers[] = $phone->getTblPhone()->getNumber() . ' ' . $type;
                            $phoneNumbers[] = $phone->getServiceTblPerson()->getLastFirstName();
                            if ($phone->getRemark()) {
                                $phoneNumbers[] = $phone->getRemark();
                            }
                        }
                    }
                }

                if (!empty($phoneNumbers)) {
                    $Item['PhoneNumbers'] = implode('<br>', $phoneNumbers);
                    $Item['ExcelPhoneNumbers'] = $phoneNumbers;
                }

                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {
                    $isSet = false;
                    // Profil
                    $tblStudentProfile = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent,
                        Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE')
                    );
                    if ($tblStudentProfile && ($tblSubject = $tblStudentProfile[0]->getServiceTblSubject())) {
                        $Item['Orientation'] = $tblSubject->getAcronym();
                        $isSet = true;
                    }
                    // Neigungskurs
                    if (!$isSet) {
                        $tblStudentOrientation = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                            $tblStudent,
                            Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION')
                        );
                        if ($tblStudentOrientation && ($tblSubject = $tblStudentOrientation[0]->getServiceTblSubject())) {
                            $Item['Orientation'] = $tblSubject->getAcronym();
                            $isSet = true;
                        }
                    }
                    // Vertiefungskurs
                    if (!$isSet) {
                        $tblStudentAdvanced = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                            $tblStudent,
                            Student::useService()->getStudentSubjectTypeByIdentifier('ADVANCED')
                        );
                        if ($tblStudentAdvanced && ($tblSubject = $tblStudentAdvanced[0]->getServiceTblSubject())) {
                            $Item['Orientation'] = $tblSubject->getAcronym();
//                            $isSet = true;
                        }
                    }

                    $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                    if ($tblTransferType) {
                        $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                            $tblTransferType);
                        if ($tblStudentTransfer) {
                            $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                            if ($tblCourse) {
                                if ($tblCourse->getName() == 'Gymnasium') {
                                    $Item['Education'] = 'GY';
                                } elseif ($tblCourse->getName() == 'Hauptschule') {
                                    $Item['Education'] = 'HS';
                                } elseif ($tblCourse->getName() == 'Realschule') {
                                    $Item['Education'] = 'RS';
                                } else {
                                    $Item['Education'] = $tblCourse->getName();
                                }
                            }
                        }
                    }
                }

                array_push($TableContent, $Item);
                // ToDo JohK zusammenfassung am Ende
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     * @param $DivisionId
     *
     * @return bool|\SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createPrintClassListExcel($PersonList, $tblPersonList, $DivisionId)
    {

        if (!empty($PersonList)) {

            // sortieren
            foreach ($PersonList as $key => $row) {
                $sort[$key] = strtoupper($row['ExcelNameRow1']);
            }
            array_multisort($sort, SORT_ASC, $PersonList);

            $fileLocation = Storage::useWriter()->getTemporary('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            if (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
                $teacherList = array();
                $tblDivisionTeacherAll = Division::useService()->getTeacherAllByDivision($tblDivision);
                if ($tblDivisionTeacherAll) {
                    foreach ($tblDivisionTeacherAll as $tblPerson) {
                        $teacherList[] = trim($tblPerson->getSalutation() . ' ' . $tblPerson->getLastName());
                    }
                }

                $export->setStyle($export->getCell(0, 0), $export->getCell(6, 0))->setFontBold();
                $export->setValue($export->getCell(0, 0),
                    "Klasse " . $tblDivision->getDisplayName() . (empty($teacherList) ? '' : ' ' . implode(', ',
                            $teacherList)));
            }

            // Header
            $export->setValue($export->getCell(0, 1), "Name");
            $export->setValue($export->getCell(1, 1), "Geb.-Datum");
            $export->setValue($export->getCell(2, 1), "Adresse");
            $export->setValue($export->getCell(3, 1), "Telefonnummer");
            $export->setValue($export->getCell(4, 1), "Gr");
            $export->setValue($export->getCell(5, 1), "NK");
            $export->setValue($export->getCell(6, 1), "BG");
            // Header bold
            $export->setStyle($export->getCell(0, 1), $export->getCell(6, 1))->setFontBold();

            $Row = 2;
            foreach ($PersonList as $PersonData) {
                $rowPerson = $Row;
                $export->setValue($export->getCell(0, $Row), $PersonData['ExcelNameRow1']);
                $export->setValue($export->getCell(1, $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell(2, $Row), $PersonData['ExcelAddressRow1']);
                $export->setValue($export->getCell(4, $Row), $PersonData['Group']);
                $export->setValue($export->getCell(5, $Row), $PersonData['Orientation']);
                $export->setValue($export->getCell(6, $Row), $PersonData['Education']);

                $Row++;
                $export->setValue($export->getCell(0, $Row), $PersonData['ExcelNameRow2']);
                $export->setValue($export->getCell(2, $Row), $PersonData['ExcelAddressRow2']);
                $Row++;

                if (!empty($PersonData['ExcelPhoneNumbers'])) {
                    foreach ($PersonData['ExcelPhoneNumbers'] as $phone) {
                        $export->setValue($export->getCell(3, $rowPerson++), $phone);
                    }
                }

                if ($rowPerson > $Row) {
                    $Row = $rowPerson;
                }

                // Gittertrennlinie
                $export->setStyle($export->getCell(0, $Row - 1), $export->getCell(6, $Row - 1))->setBorderBottom();
            }

            // Gitterlinien
            $export->setStyle($export->getCell(0, 1), $export->getCell(6, 1))->setBorderBottom();
            $export->setStyle($export->getCell(0, 1), $export->getCell(6, $Row - 1))->setBorderVertical();
            $export->setStyle($export->getCell(0, 1), $export->getCell(6, $Row - 1))->setBorderOutline();

            // Personenanzahl
            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Weiblich:');
            $export->setValue($export->getCell(1, $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Männlich:');
            $export->setValue($export->getCell(1, $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Gesamt:');
            $export->setValue($export->getCell(1, $Row), count($tblPersonList));

            // Stand
            $Row += 2;
            $export->setValue($export->getCell(0, $Row), 'Stand: ' . (new \DateTime())->format('d.m.Y'));

            // Spaltenbreite
            $export->setStyle($export->getCell(0, 0), $export->getCell(0, $Row))->setColumnWidth(22);
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $Row))->setColumnWidth(10);
            $export->setStyle($export->getCell(2, 0), $export->getCell(2, $Row))->setColumnWidth(23);
            $export->setStyle($export->getCell(3, 0), $export->getCell(3, $Row))->setColumnWidth(23);
            $export->setStyle($export->getCell(4, 0), $export->getCell(4, $Row))->setColumnWidth(4);
            $export->setStyle($export->getCell(5, 0), $export->getCell(6, $Row))->setColumnWidth(5);
            $export->setStyle($export->getCell(6, 0), $export->getCell(6, $Row))->setColumnWidth(3.5);

            // Schriftgröße
            $export->setStyle($export->getCell(0, 0), $export->getCell(6, 0))->setFontSize(12);
            $export->setStyle($export->getCell(0, 1), $export->getCell(6, $Row))->setFontSize(10);

            // Spalten zentriert
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $Row))->setAlignmentCenter();
            $export->setStyle($export->getCell(4, 0), $export->getCell(4, $Row))->setAlignmentCenter();
            $export->setStyle($export->getCell(5, 0), $export->getCell(5, $Row))->setAlignmentCenter();
            $export->setStyle($export->getCell(6, 0), $export->getCell(6, $Row))->setAlignmentCenter();

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }
}
