<?php
namespace SPHERE\Application\Setting\Univention;

use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Info as InfoText;
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
     * @return Stage
     */
    public function frontendUnivention()
    {
        $Stage = new Stage('Univention', '');
        $Stage->addButton(new Standard('Zurück', '/Setting', new ChevronLeft()));

        //ToDO Erklärung der Schnittstelle? + Vorraussetzungen

        return $Stage;
    }

    /**
     * @param bool $Upload
     *
     * @return Stage
     */
    public function frontendUnivAPI($Upload = '')
    {

        $Stage = new Stage('Univention', 'Online Verbindung');
        $Stage->addButton(new Standard('Zurück', '/Setting/Univention', new ChevronLeft()));

        $beginn = microtime(true);
        // dynamsiche Rollenliste
        $roleList = (new UniventionRole())->getAllRoles();

        $dauer = microtime(true) - $beginn;
        $dauer = round($dauer, 2);
        echo "holen aller Rollen aus der API: $dauer Sek.</br>";

        $beginn = microtime(true);

        // dynamsiche Schulliste
        $schoolList = (new UniventionSchool())->getAllSchools();

        $dauer = microtime(true) - $beginn;
        $dauer = round($dauer, 2);
        echo "holen Schulen aus der API: $dauer Sek.</br>";

        // early break if no answer
        if(!is_array($roleList) || !is_array($schoolList)){
            $Stage->setContent(new Warning('Univention liefert keine Informationen'));
            return $Stage;
        }

        // Buttons nur bei aktiver API
        // Removed for Live
        $Stage->addButton(new Standard('Accounts komplett abgleichen', '/Setting/Univention/Api', new Upload(), array('Upload' => 'All')));
        $Stage->addButton(new Standard('Accounts hinzufügen', '/Setting/Univention/Api', new Upload(), array('Upload' => 'Create')));
        $Stage->addButton(new Standard('Accounts ändern', '/Setting/Univention/Api', new Upload(), array('Upload' => 'Update')));
        $Stage->addButton(new Standard('Accounts löschen', '/Setting/Univention/Api', new Upload(), array('Upload' => 'Delete')));

        $UserUniventionList = Univention::useService()->getApiUser();


        $beginn = microtime(true);
        $ErrorLog = array();
        $UserSchulsoftwareList = array();
        $tblYear = Term::useService()->getYearByNow();
        if($tblYear){
            $tblYear = current($tblYear);
            $UserSchulsoftwareList = Univention::useService()->getSchulsoftwareUser($tblYear, $roleList, $schoolList);
        } else {
            $ErrorLog[] = 'kein aktuelles Jahr gefunden';
        }

        $dauer = microtime(true) - $beginn;
        $dauer = round($dauer, 2);
        echo "holen aller Benutzeraccounts aus der Schulsoftware * vorbereitung, das Werte wie in der API gepflegt werden: $dauer Sek. (nur 12 Benutzer in der Schulsoftware)</br>";

        // Vergleich
        // Zählung
        $count['create'] = 0;
        $count['noCreate'] = 0;
        $count['update'] = 0;
        $count['noUpdate'] = 0;
        $count['delete'] = 0;
        // create: AccountActive welche nicht in der API vorhanden sind
        //ToDO eigene Variablen, nicht alles in einem Riesen array
        $createList = array();
        $cantCreateList = array();
//        $ApiList['Create'] = array();
        $ApiList['noCreate'] = array();

        // update: Accounts welche Vorhanden sind, aber unterschiedliche Werte aufweisen
        $updateList = array();
        $cantUpdateList = array();
        $ApiList['Update'] = array();
        $ApiList['noUpdate'] = array();

        // Display changes
        $UpdateLog = array();
        // delete: Accounts, die in der API vorhaden sind, aber nicht in der Schulsoftware
        $deleteList = array();
        $ApiList['Delete'] = array();

        $beginn = microtime(true);

        if(!empty($UserSchulsoftwareList)){
            foreach($UserSchulsoftwareList as $AccountActive){
                if(!isset($UserUniventionList[$AccountActive['record_uid']])
                ){
                    if(($Error = $this->controlAccount($AccountActive))){
                        $cantCreateList[] = $Error;
                        $count['noCreate']++;
                    } else {
                        $count['create']++;
                        $createList[] = $AccountActive;
                        // Lokaler Test
//                    $createList[] = $AccountActive['name'];
                    }
                    unset($UserUniventionList[$AccountActive['record_uid']]);
                } else {
                    $Log = array();
                    //ToDO only Update if necessary
                    // compare
                    $ExistUser = $UserUniventionList[$AccountActive['record_uid']];
                    if($ExistUser['firstname'] != $AccountActive['firstname']){
                        $Log[] = 'Vorname: '.new InfoText($ExistUser['firstname']).' -> '.new SuccessText($AccountActive['firstname']);
                    }
                    if($ExistUser['lastname'] != $AccountActive['lastname']){
                        $Log[] = 'Nachname: '.new InfoText($ExistUser['lastname']).' -> '.new SuccessText($AccountActive['lastname']);
                    }
//                if($ExistUser['birthday'] != $AccountActive['birthday']){
//                    $Log[] = 'Geburtstag: '.new InfoText($ExistUser['birthday']).' -> '.new SuccessText($AccountActive['birthday']);
//                }
                    if($ExistUser['email'] != $AccountActive['email']){
                        $Log[] = 'E-Mail: '.new InfoText($ExistUser['email']).' -> '.new SuccessText($AccountActive['email']);
                    }
                    if($ExistUser['roles'] != $AccountActive['roles']){
                        $Log[] = 'Rolle: '.new InfoText(new Listing($ExistUser['roles'])).' -> '.new SuccessText(new Listing($AccountActive['roles']));
                    }
                    if($ExistUser['schools'] != $AccountActive['schools']){
                        $Log[] = 'Schule: '.new InfoText(new Listing($ExistUser['schools'])).' -> '.new SuccessText(new Listing($AccountActive['schools']));
                    }
//                if($ExistUser['school_classes'] != $AccountActive['school_classes']){
//                    $Log[] = 'Klasse(n): '.new InfoText(new Listing($ExistUser['school_classes'])).' -> '.new SuccessText(new Listing($ExistUser['school_classes']));
//                }
                    if(($Error = $this->controlAccount($AccountActive))){
                        $cantUpdateList[] = $Error;
                        $count['noUpdate']++;
                    } else {
                        $count['update']++;
                        $updateList[] = $AccountActive;
//                        $UpdateLog[$AccountActive['record_uid']] = $Log; // brauch ich das noch?
                    }
                }
                unset($UserUniventionList[$AccountActive['record_uid']]);
            }
            $count['delete'] = count($UserUniventionList);
            $deleteList = $UserUniventionList;
        }

        $ErrorLog = array_filter($ErrorLog);



        $dauer = microtime(true) - $beginn;
        $dauer = round($dauer, 2);
        echo "Entscheidung welche Funktionen ausgeführt werden müssen + Frontendvorbereitung: $dauer Sek.)</br>";

//        echo 'Api Kommunikation';
//        Debugger::screenDump($ApiList);
//        echo'Zählung:';
//        Debugger::screenDump($count);
//        echo'ErrorLog:';
//        Debugger::screenDump($ErrorLog);

        $beginn = microtime(true);

        $returnString = 'Komplett Abgleich';
        if($Upload == 'Create'){
            $returnString = 'Hinzufügen';
        } elseif($Upload == 'Update'){
            $returnString = 'Ändern';
        } elseif($Upload == 'Delete'){
            $returnString = 'Löschen';
        }

        // Upload erst nach ausführlicher Bestätigung
        if($Upload == 'All' || $Upload == 'Create'){
            foreach($createList as $createAccount){

                //ToDO Remove if for local test
                //Lokal Test Performance
//                if($createAccount['name'] == 'REF-ZiEh15'
//                || $createAccount['name'] == 'REF-PyZi01'
//                || $createAccount['name'] == 'REF-RoSt22'
//                || $createAccount['name'] == 'REF-QuKa01'
//                || $createAccount['name'] == 'REF-FiRo22'){
                    // create with API
                    $ErrorLog[] = (new UniventionUser())->createUser($createAccount['name'], $createAccount['email'],
                        $createAccount['firstname'], $createAccount['lastname'], $createAccount['record_uid'],
                        $createAccount['roles'], $createAccount['schools'], $createAccount['school_classes'],
                        $createAccount['source_uid']);
//                }
            }
            $Stage = new Stage('Univention', 'Service');
            $Stage->setContent(new Success($returnString.' durchgeführt')    );
//            . new Redirect('/Setting/Univention/Api', Redirect::TIMEOUT_SUCCESS));

            $dauer = microtime(true) - $beginn;
            $dauer = round($dauer, 2);
            echo "Kommunikation mit der API um Veränderungen auszuführen: $dauer Sek.)</br>";
            return $Stage;
        }


        if($Upload == 'All' || $Upload == 'Update'){
            foreach($updateList as $updateAccount){
                //ToDO Update mach funktionstüchtigkeit und Feldinformation anpassen
//                // update with API
//                (new UniventionUser())->updateUser($updateAccount['name'], $updateAccount['email'],
//                    $updateAccount['firstname'], $updateAccount['lastname'], $updateAccount['record_uid'],
//                    $updateAccount['roles'], $updateAccount['schools'], $updateAccount['source_uid']);
            }
            $Stage = new Stage('Univention', 'Service');
            $Stage->setContent(new Success($returnString.' durchgeführt')    );
//            . new Redirect('/Setting/Univention/Api', Redirect::TIMEOUT_SUCCESS));

            $dauer = microtime(true) - $beginn;
            $dauer = round($dauer, 2);
            echo "Kommunikation mit der API um Veränderungen auszuführen: $dauer Sek.)</br>";
            return $Stage;
        }
        if($Upload == 'All' || $Upload == 'Delete'){
            // ToDo Nutzer in Univention löschen, wenn sie in der Schulsoftware gelöscht werden?
            // ToDO Klärung in wie weit das gewünscht / erfordert ist.
            // ToDO Löschen funktioniert wahrscheinlich nicht mit Umlauten, warten auf Antwort
            foreach($deleteList as $deleteAccount){
                // delete with API
                $ErrorLog[] = (new UniventionUser())->deleteUser($deleteAccount);
            }
            $Stage = new Stage('Univention', 'Service');
            $Stage->setContent(new Success($returnString.' durchgeführt')    );
//            . new Redirect('/Setting/Univention/Api', Redirect::TIMEOUT_SUCCESS));

            $dauer = microtime(true) - $beginn;
            $dauer = round($dauer, 2);
            echo "Kommunikation mit der API um Veränderungen auszuführen: $dauer Sek.)</br>";

            return $Stage;
        }

        $dauer = microtime(true) - $beginn;
        $dauer = round($dauer, 2);
        echo "Kommunikation mit der API um Veränderungen auszuführen (keine Aktion ausgeführt): $dauer Sek.)</br>";

        $beginn = microtime(true);

        $ContentCreate = array();
        $ContentUpdate = array();
        $ContentDelete = array();
        //Type = Create/Update/Delete
//        Debugger::screenDump($ApiList);
        foreach($createList as $AccountArray) {
            $DivisionString = 'Klasse: ';
            if(strpos($AccountArray['school_classes'], ',')){
                $DivisionString = 'Klassen: ';
            }

            $ContentCreate[] = (new ToolTip($AccountArray['name'].' '.new Info(), htmlspecialchars(
                $AccountArray['firstname'].' '.$AccountArray['lastname'].'<br/>'.
                $DivisionString.$AccountArray['school_classes']
            )))->enableHtml();
        }
        foreach($updateList as $AccountArray) {
            // ToDo Display changes that will be happend
            $DivisionString = 'Klasse: ';
            if(strpos($AccountArray['school_classes'], ',')){
                $DivisionString = 'Klassen: ';
            }
            if(isset($UpdateLog[$AccountArray['record_uid']]) && !empty($UpdateLog[$AccountArray['record_uid']])){
                $ContentUpdate[] = (new ToolTip($AccountArray['name'].' '.new Info(), htmlspecialchars(
                    implode('<br/>', $UpdateLog[$AccountArray['record_uid']])
                )))->enableHtml();
            } else {
                $ContentUpdate[] = $AccountArray['name'];
            }
        }
        foreach($deleteList as $AccountArray) {
            $ContentDelete[] = (new ToolTip($AccountArray['name'].' '.new Info(), htmlspecialchars(
                $AccountArray['firstname'].' '.$AccountArray['lastname']
//                        .'<br/>'.
//                        $DivisionString.$Account['school_classes']
            )))->enableHtml();
        }

        $NoCreatePanelContent = '';
        if(!empty($ApiList['noCreate'])){
            foreach($ApiList['noCreate'] as $AccountErrorList){
                if(!empty($AccountErrorList)){
                    $NoCreatePanelContent[] = new Listing($AccountErrorList);
                }
            }
        }

        $Stage->setContent(new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    new Panel('Neue Benutzer für Connexion',
                        $ContentCreate, Panel::PANEL_TYPE_INFO
                    )
                , 4),
                new LayoutColumn(
                    new Panel('Benutzer anpassen',
                        $ContentUpdate, Panel::PANEL_TYPE_PRIMARY
                    )
                , 4),
                new LayoutColumn(
                    new Panel('Benutzer in Connexion entfernen',
                        $ContentDelete, Panel::PANEL_TYPE_DANGER
                    )
                , 4)
            )),
            new LayoutRow(array(
                new LayoutColumn(
                    new Panel('Benutzerm die nicht angelegt werden können',
                        $NoCreatePanelContent, Panel::PANEL_TYPE_WARNING
                    )
                , 4),
                new LayoutColumn(
                    new Panel('Benutzer, die nicht bearbeitet werden können',
                        $ApiList['noUpdate'], Panel::PANEL_TYPE_WARNING
                    )
                , 4),
            ))
            ))));

        $dauer = microtime(true) - $beginn;
        $dauer = round($dauer, 2);
        echo "Frontend anzeigen: $dauer Sek.)</br>";

        return $Stage;
    }

    /**
     * @param array $Account
     * correct Account values return false
     * incorrect Accounts return ErrorLog
     *
     * @return array|bool
     */
    public function controlAccount($Account = array())
    {

        $ErrorLog = array();
        // Handle Error Entity
        if($Account['name'] == ''
            || $Account['firstname'] == ''
            || $Account['lastname'] == ''
            || $Account['record_uid'] == ''
            || $Account['source_uid'] == ''
            || $Account['school_classes'] == ''
            || empty($Account['roles'])
            || empty($Account['schools'])) {

            $ErrorLog[] = new Bold($Account['name']);

            foreach($Account as $Key => $Value){
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
                        $ErrorLog[] = $Key.' '.new DangerText('nicht vorhanden! ').$MouseOver;
                    }else {
                        $ErrorLog[] = $Key.' Ok';
                    }

                }else {
                    if($Value === ''){
                        $MouseOver = '';
                        switch ($Key){
                            case 'lastname':
                                $MouseOver = new ToolTip(new Info(), 'keine Person am Account');
                                break;
                            case 'school_classes':
                                $MouseOver = new ToolTip(new Info(), 'Person muss mindestens einer Klasse zugewiesen sein');
                                break;
                        }
                        $ErrorLog[] = $Key.' '.new DangerText('nicht vorhanden! ').$MouseOver;
                    }
                }
            }
        }
        return (!empty($ErrorLog) ? $ErrorLog : false);
    }

//        // Benutzer anlegen
//        $ErrorLog[] = (new UniventionUser())->createUser('MaxMustermann', 'Kukane', 'Klimpel', '7', array($roleList['student']),
//            array($schoolList['DEMOSCHOOL'], $schoolList['DEMOSCHOOL2']), $Acronym.'-7');
//        $ErrorLog[] = (new UniventionUser())->createUser('MustermannMax', 'Valentina', 'Allgaier', '8', array($roleList['staff'],$roleList['teacher']),
//            array($schoolList['DEMOSCHOOL']), $Acronym.'-8');
//
//        // Benutzerliste suchen
//        $UserList = (new UniventionUser())->getUserListByProperty('name','ref-', true);
//        Debugger::screenDump($UserList);
//        // Benutzer entfernen
//        if($UserList){
//            foreach($UserList as $ResourceUser){
//                Debugger::screenDump(utf8_encode($ResourceUser->name));
//                Debugger::screenDump($ResourceUser->name);
//                $ErrorLog[] = (new UniventionUser())->deleteUser($ResourceUser->name);
//            }
//        }
//
//        $ErrorLog[] = (new UniventionUser())->deleteUser('DEMO-login');

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