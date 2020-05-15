<?php
namespace SPHERE\Application\Setting\Univention;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Setting\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Univention
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param bool $Upload
     *
     * @return Stage
     */
    public function frontendUnivention()
    {
        $Stage = new Stage('Univention', '');
        $Stage->addButton(new Standard('Zurück', '/Setting', new ChevronLeft()));

        //ToDO Erklärung der Schnittstelle? + Vorraussetzungen

        return $Stage;
    }

    public function frontendUnivAPI($Upload = false)
    {

        $Stage = new Stage('Univention', 'Online Verbindung');
        $Stage->addButton(new Standard('Zurück', '/Setting/Univention', new ChevronLeft()));
        // Removed for Live
//        $Stage->addButton(new Standard('Accounts übertragen', '/Setting/Univention/Api', new Upload(), array('Upload' => true)));

//        $beginn = microtime(true);
        // dynamsiche Rollenliste
        $roleList = (new UniventionRole())->getAllRoles();
//        Debugger::screenDump($roleList);

        // dynamsiche Schulliste
        $schoolList = (new UniventionSchool())->getAllSchools();
//        Debugger::screenDump($schoolList);

//        $dauer = microtime(true) - $beginn;
//        echo "Verbindung zur API: $dauer Sek.</br>";

        $Acronym = Account::useService()->getMandantAcronym();
        $tblAccountList = Univention::useService()->getAccountAllForAPITransfer();
//        $TargetDate = new \DateTime('12.03.2020');
        $TargetDate = false;

        $UploadToAPI = array();
        $ErrorLog = array();
        $AccountError = array();
        $countUploadAccount = 0;
        $countUploadAccountError = 0;

        $tblYear = Term::useService()->getYearByNow();
        if($tblYear){
            $tblYear = current($tblYear);
        } else {
            $ErrorLog[] = 'kein aktuelles Jahr gefunden';
        }

        foreach($tblAccountList as $tblAccount){

            $DateCompare = array();

            $UserName = $tblAccount->getUsername();
            Univention::useService()->setDateList($tblAccount, $DateCompare);

            $UploadItem['name'] = $UserName;
            $UploadItem['firstname'] = '';
            $UploadItem['lastname'] = '';
            $UploadItem['record_uid'] = $tblAccount->getId();
            $UploadItem['source_uid'] = $Acronym.'-'.$tblAccount->getId();
            $UploadItem['roles'] = '';
            $UploadItem['schools'] = '';

            $UploadItem['password'] = $tblAccount->getPassword(); // ??
            $UploadItem['school_classes'] = '';
            $UploadItem['group'] = ''; // ?? Stammgruppen

            $tblPerson = Account::useService()->getPersonAllByAccount($tblAccount);
            if($tblPerson){
                $tblPerson = current($tblPerson);
                $UploadItem['firstname'] = $tblPerson->getFirstName();
                $UploadItem['lastname'] = $tblPerson->getLastName();
                Univention::useService()->setDateList($tblPerson, $DateCompare);
            } else {
                $ErrorLog[] = $UserName.': Account ohne Person';
                continue;
            }
            // Rollen
            $tblGroupList = Group::useService()->getGroupAllByPerson($tblPerson);
            $roles = array();
            foreach($tblGroupList as $tblGroup){
                if($tblGroup->getMetaTable() === TblGroup::META_TABLE_STAFF){
                    $roles[] = $roleList['staff'];
                }
                if($tblGroup->getMetaTable() === TblGroup::META_TABLE_TEACHER){
                    $roles[] = $roleList['teacher'];
                }
                if($tblGroup->getMetaTable() === TblGroup::META_TABLE_STUDENT){
                    $roles[] = $roleList['student'];
                }
            }
            $UploadItem['roles'] = $roles;

            $tblDivision = false;
            if ($tblYear){
                ($tblDivision = Division::useService()->getDivisionByPersonAndYear($tblPerson, $tblYear));
            }
            // Student Search Division
            $StudentSchool = '';
            // Schulen (alle) //ToDO Schulstring erzeugen
            $schools = array();
            if($tblStudent = Student::useService()->getStudentByPerson($tblPerson)){
                $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier(TblStudentTransferType::PROCESS);
                if(($tblStudentTransfer =  Student::useService()->getStudentTransferByType($tblStudent, $tblStudentTransferType))){
                    if(($tblCompany = $tblStudentTransfer->getServiceTblCompany())){
                        if($tblDivision){
                            // Schule über Schülerakte Company und Klasse (Schulart)
                            if(($tblSchoolType = $tblDivision->getType())){
                                $SchoolTypeString = Type::useService()->getSchoolTypeString($tblSchoolType);
                                $SchoolString = $Acronym.$SchoolTypeString.$tblCompany->getId();
                                $StudentSchool = $SchoolString;
                                if(isset($schoolList[$SchoolString])){
                                    $schools[] = $SchoolString;
                                } else {
                                    $ErrorLog[] = 'Schule '.$SchoolString.' nicht in der API vorhanden';
                                }
//                                // ToDO Schoolstring aus Array
//                                // $schools[] = $schoolList[$schoolString];
                            }
                        }
                    }
                }
            } else {
                // keine Schüler -> Accunt bekommt alle Schulen des Mandanten
                if(($tblSchoolList =  School::useService()->getSchoolAll())){
                    foreach($tblSchoolList as $tblSchool){
                        $tblCompany = $tblSchool->getServiceTblCompany();
                        $tblSchoolType = $tblSchool->getServiceTblType();
                        if($tblCompany && $tblSchoolType){
                            $SchoolTypeString = Type::useService()->getSchoolTypeString($tblSchoolType);
                            $schoolString = $Acronym.$SchoolTypeString.$tblCompany->getId();
                            if(isset($schoolList[$schoolString])){
                                $schools[] = $schoolString;
                                // ToDO Schoolstring aus Array
//                                $schools[] = $schoolList[$schoolString];
                            } else {
                                $ErrorLog[] = 'Schule '.$schoolString.' nicht in der API vorhanden';
                            }
                        }
                    }
                }
            }

            if($tblDivision){
                $UploadItem['school_classes'] = $StudentSchool.'-'.$tblDivision->getTblLevel()->getName().$tblDivision->getName();
                $tblDivisionStudent = Division::useService()->getDivisionStudentByDivisionAndPerson($tblDivision, $tblPerson);
                Univention::useService()->setDateList($tblDivisionStudent, $DateCompare);
            }

//            // Uploadtest
//            if($tblStudent = Student::useService()->getStudentByPerson($tblPerson)){
//                if(rand(0, 1)){
//                    $schools = array(
//                        $schoolList['DEMOSCHOOL']
//                    );
//                } else {
//                    $schools = array(
//                        $schoolList['DEMOSCHOOL2']
//                    );
//                }
//            } else {
//                $schools = array(
//                    $schoolList['DEMOSCHOOL'],
//                    $schoolList['DEMOSCHOOL2']
//                );
//            }

            $UploadItem['schools'] = $schools;

//            // alle Accounts ignorieren, die keine Updates erfahren haben.
//            $IsUpdateRelevant = false;
//            if(!$TargetDate){
//                // Erstexport (Es gibt kein Datum des letzten Uploads)
            $IsUpdateRelevant = true;
//            } elseif(!empty($DateCompare)){
//                foreach($DateCompare as $CrateUpdateDate){
//                    if($CrateUpdateDate >= $TargetDate){
//                        $IsUpdateRelevant = true;
//                    }
//                }
//            }

            if($IsUpdateRelevant){
                $countUploadAccount++;
//                $DateCompare['name'] = $UploadItem['name'];
//                Debugger::screenDump($DateCompare);
            }


            // Handle Error Entity
            if($IsUpdateRelevant
                && $UploadItem['name'] !== ''
                && $UploadItem['firstname'] !== ''
                && $UploadItem['lastname'] !== ''
                && $UploadItem['record_uid'] !== ''
                && $UploadItem['source_uid'] !== ''
                && !empty($UploadItem['roles'])
                && !empty($UploadItem['schools'])) {
                array_push($UploadToAPI, $UploadItem);
            } elseif($IsUpdateRelevant) {
                $countUploadAccountError++;
                foreach($UploadItem as $Key => &$Value){
                    if(is_array($Value)){

                        $MouseOver = '';
                        switch ($Key){
                            case 'roles':
                                $MouseOver = (new ToolTip(new Info(), htmlspecialchars(
                                    'Mögliche Fehler:</br>'
                                    .'Schüler '.new DangerText('keine aktive Klasse').'</br>'
                                    .'Person in keiner der Folgenen Personengruppen:</br>'
                                    .new DangerText('Schüler / Mitarbeiter / Lehrer')
                                )))->enableHtml();
                                break;
                            case 'schools':
                                $MouseOver = (new ToolTip(new Info(), htmlspecialchars(
                                    'Schüler ist keiner Klasse zugewiesen </br>'
                                    .'oder Schule fehlt in Univention')))->enableHtml();
                                break;
                        }

                        if(empty($Value)){
                            $Value = $Key.' '.new DangerText('nicht vorhanden! ').$MouseOver;
                        }else {
                            $Value = $Key.' Ok';
                        }

                    }else {
                        if($Value === ''){
                            $MouseOver = '';
                            switch ($Key){
                                case 'Test':
                                    $MouseOver = new ToolTip(new Info(), 'TEXT einfügen'); //ToDO welche Felder bedüfen einer Info zur fehlerbehebung
                                    break;
                            }
                            $Value = $Key.' '.new DangerText('nicht vorhanden! ').$MouseOver;
                        }
                    }
                }
                array_push($AccountError, $UploadItem);
            }
        }

        $LayoutColumnList = array();
        if(!empty($AccountError)){
            foreach($AccountError as $ErrorEntity){
                $LayoutColumnList[] = new LayoutColumn(new Panel($ErrorEntity['name'], array(
                    $ErrorEntity['firstname'],
                    $ErrorEntity['lastname'],
//                    $ErrorEntity['record_uid'],
//                    $ErrorEntity['source_uid'],
                    $ErrorEntity['roles'],
                    $ErrorEntity['schools'],
                )), 3);
            }
        }

        $ErrorPanel = false;
        $ErrorLog = array_unique($ErrorLog);
        if(!empty($ErrorLog)){
            {
                $ErrorPanel = new Panel(new Bold('Fehler zur Univention-Schnittstelle'), $ErrorLog, Panel::PANEL_TYPE_DANGER);
            }
        }

        if($Upload){
            foreach($UploadToAPI as $Account){
                //ToDO erkennen was gepatcht und was createt werden muss.
//                (new UniventionUser())->createUser($Account['name'], $Account['firstname'], $Account['lastname'], $Account['record_uid'], $Account['roles'],
//                    $Account['schools'], $Account['source_uid']);

                (new UniventionUser())->updateUser($Account['name'], $Account['firstname'], $Account['lastname'], $Account['record_uid'], $Account['roles'],
                    $Account['schools'], $Account['source_uid']);
            }

        }

//        // Benutzerliste suchen
//        $UserList = (new UniventionUser())->getUserListByName('ref-', false);
//        Debugger::screenDump($UserList);
//        $UserList = (new UniventionUser())->getUserListByName('demo-', false);
//        Debugger::screenDump($UserList);

        $Stage->setContent(new Layout(
            new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(($ErrorPanel
                        ? $ErrorPanel
                        : '')),
//                    new LayoutColumn(new Listing($DateCompare)),
                )),
                new LayoutRow(
                    new LayoutColumn(
                        new Title('Es stehen '.$countUploadAccount.' Accounts zum Upload zur Verfügung, '.
                            $countUploadAccountError.' dieser Accounts können nicht hochgeladen werden.')
                    )
                ),
                new LayoutRow(
                    new LayoutColumn(
                        (!empty($LayoutColumnList)
                            ? new Title('Accounts', 'die nicht hochgeladen werden können')
                            : '')
                    )
                ),
                new LayoutRow(
                    $LayoutColumnList
                )
            ))
        ));


        return $Stage;
    }

//        // Benutzer anlegen
//        $ErrorLog[] = (new UniventionUser())->createUser('MaxMustermann', 'Kukane', 'Klimpel', '7', array($roleList['student']),
//            array($schoolList['DEMOSCHOOL'], $schoolList['DEMOSCHOOL2']), $Acronym.'-7');
//        $ErrorLog[] = (new UniventionUser())->createUser('MustermannMax', 'Valentina', 'Allgaier', '8', array($roleList['staff'],$roleList['teacher']),
//            array($schoolList['DEMOSCHOOL']), $Acronym.'-8');
//
//        // Benutzerliste suchen
//        $UserList = (new UniventionUser())->getUserListByName('demo-', false);
//        Debugger::screenDump($UserList);
//
//        // Benutzer entfernen
//        if($UserList){
//            foreach($UserList as $Name){
//                $ErrorLog[] = (new UniventionUser())->deleteUser($Name);
//            }
//        }
//
//        $ErrorLog[] = (new UniventionUser())->deleteUser('DEMO-login');
//        $ErrorLog[] = (new UniventionUser())->deleteUser('DEMO-login2');

    /**
     * @return Stage
     */
    public function frontendUnivCSV()
    {
        $Stage = new Stage('Univention', 'Online Verbindung');
        $Stage->addButton(new Standard('Zurück', '/Setting/Univention', new ChevronLeft()));
        $Stage->addButton(new Standard('CSV Schulen herunterladen', '/Api/Reporting/Univention/SchoolList/Download', new Download(), array(), 'Schulen aus den Mandanten Einstellungen'));
        $Stage->addButton(new Standard('CSV User herunterladen', '/Api/Reporting/Univention/User/Download', new Download(), array(), 'Es werden auch Schüler ohne Account hinzugefügt.'));

        $ErrorLog = array();
        if(($AccountPrepareList = Univention::useService()->getExportAccount(true))){

            $isCoreGroupUsage = false;
            // kontrolle Stammgruppennutzung
            if(Group::useService()->getGroupListByIsCoreGroup()){
                $isCoreGroupUsage = true;
            }

            foreach($AccountPrepareList as $Data){
                $IsError = false;
//                $Data['name'];
//                $Data['firstname'];       // Account ohne Person wird bereits davor ausgefiltert
//                $Data['lastname'];
//                $Data['record_uid'];      // Accountabhängig
//                $Data['source_uid'];      // Accountabhängig
//                $Data['roles'];           // benutzer ohne Rollen werden bereits entfernt. nachträglich werden Schüler herrangezogen, die besitzen immer Student
//                $Data['schools'];
//                $Data['password'];        // noch keine Prüfung
//                $Data['school_classes'];
//                $Data['groupArray'];


//                if($Data['name'] == 'DEMO-login'){
//                    var_dump($Data['name']);
//                    var_dump($Data['schools']);
//                    var_dump($Data['school_classes']);
//                }
                if(!$Data['name']){
                    $Data['name'] = (new ToolTip(new Exclamation(), htmlspecialchars(new Minus().' Person als '.
                        new Bold('Schüler').' besitzt keinen Account')))->enableHtml().
                        new DangerText('Account fehlt ');
                    $IsError = true;
                }
                if(!$Data['schools']){
                    $Data['schools'] = (new ToolTip(new Exclamation(),
                        htmlspecialchars(new Minus().' Lehrer erhält alle Schulen aus Mandanteneinstellungen<br/>'
                            .new Minus().' Schüler benötigt aktuelle Klasse<br/>'
                            .new Minus().' Schüler benötigt aktuelle Schule in S-Akte'
                        )))->enableHtml().
                        new DangerText(' Keine Schule hinterlegt');
                    $IsError = true;
                } else {
                    $Data['schools'] = new SuccessText(new SuccessIcon().' gefunden');
                }
                if(!$Data['school_classes'] && preg_match("/student/",$Data['roles'])){
                    $Data['school_classes'] = (new ToolTip(new Exclamation(), htmlspecialchars(new Minus().
                            ' Schüler benötigt eine aktuelle Klasse')))->enableHtml().
                            new DangerText(' Keine Klasse');
                    $IsError = true;
                } else {
                    $Data['school_classes'] = new SuccessText(new SuccessIcon().' gefunden');
                }

                // Stammgruppe nur für Schüler
                if($isCoreGroupUsage && empty($Data['groupArray']) && preg_match("/student/",$Data['roles'])){
                    $Data['group'] = new DangerText('Keine Stammgruppe');
                    $IsError = true;
                } elseif($isCoreGroupUsage && count($Data['groupArray']) > 1 && preg_match("/student/",$Data['roles'])){
                    $Data['group'] = new DangerText('mehr als eine Stammgruppe: '.implode(', ',$Data['group']));
                    $IsError = true;
                } elseif($isCoreGroupUsage && preg_match("/student/",$Data['roles'])){
                    $Data['group'] = $Data['school_classes'] = new SuccessText(new SuccessIcon().' '.$Data['groupArray'][0]);
                }

                if($IsError){
                    $ErrorLog[] = $Data;
                }
            }
        }

        $Columnlist = array();
        if(!empty($ErrorLog)){
            foreach ($ErrorLog as $Notification){
                $PanelContent = array();
                $PanelContent[] = 'Person: '.$Notification['firstname'].' '. $Notification['lastname'];
                $PanelContent[] = 'Schule: '.$Notification['schools'];
                $PanelContent[] = 'Klasse: '.$Notification['school_classes'];
                if(isset($Notification['group'])){
                    $PanelContent[] = 'Stammgruppe: '.$Notification['group'];
                }

                $Columnlist[] = new LayoutColumn(
                    new Panel($Notification['name'], $PanelContent)
                , 2);
            }
        }

        $Stage->setcontent(new Layout(new LayoutGroup(array(
            new LayoutRow(
                new LayoutColumn(
                    new Title(count($Columnlist).' Warnungen')
                )
            ),
            new LayoutRow(
                $Columnlist
            )
        ))));

        return $Stage;
    }
}