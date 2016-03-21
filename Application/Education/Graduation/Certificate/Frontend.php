<?php
namespace SPHERE\Application\Education\Graduation\Certificate;

use MOC\V\Component\Document\Component\Bridge\Repository\DomPdf;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Document;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Element;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Frame;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Page;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Section;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Slice;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Search\Group\Group;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Cache\Handler\TwigHandler;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    public function frontendStudent()
    {

        $Stage = new Stage('Schüler', 'wählen');

        $tblGroup = Group::useService()->getGroupByMetaTable('STUDENT');

        $StudentTable = array();
        if ($tblGroup) {
            $tblPersonAll = Group::useService()->getPersonAllByGroup($tblGroup);
            if ($tblPersonAll) {
                array_walk($tblPersonAll, function (TblPerson $tblPerson) use (&$StudentTable) {

                    $tblDivisionStudent = Division::useService()->getDivisionStudentAllByPerson($tblPerson);
                    if ($tblDivisionStudent) {
                        array_walk($tblDivisionStudent,
                            function (TblDivisionStudent $tblDivisionStudent) use (&$StudentTable, $tblPerson) {

                                $tblDivision = $tblDivisionStudent->getTblDivision();

                                $StudentTable[] = array(
                                    'Division' => $tblDivision->getDisplayName(),
                                    'Student'  => $tblPerson->getLastFirstName(),
                                    'Option'   => new Standard(
                                        'Weiter', '/Education/Graduation/Certificate/Template', new ChevronRight(),
                                        array(
                                            'Id' => $tblDivisionStudent->getId()
                                        ), 'Auswählen')
                                );
                            }
                        );
                    }
                });
            } else {
                // TODO: Error
            }

            $Stage->setContent(
                new TableData($StudentTable)
            );

        } else {
            // TODO: Error
        }

        return $Stage;
    }

    /**
     * @param null|int $Id TblDivisionStudent
     *
     * @return Stage
     */
    public function frontendTemplate($Id = null)
    {

        $Stage = new Stage('Vorlage', 'wählen');

        if ($Id) {
            $tblDivisionStudent = Division::useService()->getDivisionStudentById($Id);
            if ($tblDivisionStudent) {
                $tblPerson = $tblDivisionStudent->getServiceTblPerson();
                if ($tblPerson) {
                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                    if ($tblStudent) {
                        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                        $tblStudentTransfer = Student::useService()->getStudentTransferByType(
                            $tblStudent, $tblStudentTransferType
                        );
                        if ($tblStudentTransfer) {
                            // TODO: Find Templates in Database (DMS)

                            $TemplateTable[] = array(
                                'Template' => 'Hauptschulzeugnis',
                                'Option'   => new Standard(
                                    'Weiter', '/Education/Graduation/Certificate/Data', new ChevronRight(), array(
                                    'Id'       => $tblDivisionStudent->getId(),
                                    'Template' => 1
                                ), 'Auswählen')
                            );

                            $Stage->setContent(
                                new Layout(array(
                                    new LayoutGroup(new LayoutRow(
                                        new LayoutColumn(array(
                                            new Panel('Aktuelle Schule: ', array(
                                                ( $tblStudentTransfer->getServiceTblCompany() ? $tblStudentTransfer->getServiceTblCompany()->getName() : 'Schule' )
                                            )),
                                            new Panel('Aktuelle Schulart: ', array(
                                                ( $tblStudentTransfer->getServiceTblType() ? $tblStudentTransfer->getServiceTblType()->getName() : 'Schulart' )
                                            )),
                                            new Panel('Aktueller Bildungsgang: ', array(
                                                ( $tblStudentTransfer->getServiceTblCourse() ? $tblStudentTransfer->getServiceTblCourse()->getName() : 'Abschluss' )
                                            )),
                                        ))
                                    ), new Title('Schüler-Informationen')),
                                    new LayoutGroup(new LayoutRow(
                                        new LayoutColumn(
                                            new TableData($TemplateTable)
                                        )
                                    ), new Title('Verfügbare Vorlagen')),
                                ))
                            );

                        } else {
                            $Stage->setContent(
                                new Warning('Vorlage kann nicht gewählt werden, da dem Schüler in der Schülerakte keine aktuelle Schulart zugewiesen wurde.')
                            );
                        }
                    } else {
                        $Stage->setContent(
                            new Warning('Vorlage kann nicht gewählt werden, da dem Schüler keine Schülerakte zugewiesen wurde.')
                            .new Standard('Zum Schüler', '/People/Person', new Person(),
                                array('Id' => $tblPerson->getId()))
                        );
                    }
                } else {
                    // TODO: Error
                }
            } else {
                $Stage->setContent(
                    new Warning('Vorlage kann nicht gewählt werden, da dem Schüler keine Klasse zugewiesen wurde.')
                );
            }
        } else {
            // TODO: Error
        }

        return $Stage;
    }

    /**
     * @param null|int $Id TblDivisionStudent
     * @param          $Template
     *
     * @return Stage
     */
    public function frontendData($Id, $Template)
    {

        $Stage = new Stage('Daten', 'eingeben');

        if ($Id) {
            $tblDivisionStudent = Division::useService()->getDivisionStudentById($Id);
            if ($tblDivisionStudent) {
                $tblPerson = $tblDivisionStudent->getServiceTblPerson();
                if ($tblPerson) {
                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                    if ($tblStudent) {

                        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                        $tblStudentTransfer = Student::useService()->getStudentTransferByType(
                            $tblStudent, $tblStudentTransferType
                        );
                        if ($tblStudentTransfer) {

                            $tblPerson = $tblStudent->getServiceTblPerson();
                            $tblDivision = $tblDivisionStudent->getTblDivision();
                            $tblYear = $tblDivision->getServiceTblYear();

                            $Global = $this->getGlobal();
                            $Global->POST['Data']['School']['Name'] = ( $tblStudentTransfer->getServiceTblCompany() ? $tblStudentTransfer->getServiceTblCompany()->getName() : 'Schule' );
                            $Global->POST['Data']['School']['Type'] = ( $tblStudentTransfer->getServiceTblType() ? $tblStudentTransfer->getServiceTblType()->getName() : 'Schulart' );
                            $Global->POST['Data']['School']['Course'] = ( $tblStudentTransfer->getServiceTblCourse() ? $tblStudentTransfer->getServiceTblCourse()->getName() : 'Abschluss' );
                            $Global->POST['Data']['School']['Year'] = $tblYear->getName();
                            $Global->POST['Data']['Name'] = $tblPerson->getLastFirstName();
                            $Global->POST['Data']['Division'] = $tblDivision->getDisplayName();
                            $Global->savePost();

                            $Stage->setContent(
                                new Layout(array(
                                    new LayoutGroup(new LayoutRow(
                                        new LayoutColumn(array(
                                            new Panel('Aktuelle Schule: ', array(
                                                ( $tblStudentTransfer->getServiceTblCompany() ? $tblStudentTransfer->getServiceTblCompany()->getName() : 'Schule' )
                                            )),
                                            new Panel('Aktuelle Schulart: ', array(
                                                ( $tblStudentTransfer->getServiceTblType() ? $tblStudentTransfer->getServiceTblType()->getName() : 'Schulart' )
                                            )),
                                            new Panel('Aktueller Bildungsgang: ', array(
                                                ( $tblStudentTransfer->getServiceTblCourse() ? $tblStudentTransfer->getServiceTblCourse()->getName() : 'Abschluss' )
                                            )),
                                        ))
                                    ), new Title('Schüler-Informationen')),
                                    new LayoutGroup(new LayoutRow(
                                        new LayoutColumn(
                                            new Form(
                                                new FormGroup(
                                                    new FormRow(array(
                                                        new FormColumn(
                                                            new Panel('Schuldaten', array(
                                                                (new TextField('Data[School][Name]', 'Schule',
                                                                    'Schule')),
                                                                (new TextField('Data[School][Type]', 'Schulart',
                                                                    'Schulart')),
                                                                (new TextField('Data[School][Course]', 'Bildungsgang',
                                                                    'Bildungsgang')),
                                                                (new TextField('Data[School][Year]', 'Schuljahr',
                                                                    'Schuljahr')),
                                                            )), 4),
                                                        new FormColumn(
                                                            new Panel('Schüler', array(
                                                                (new TextField('Data[Name]', 'Name', 'Name')),
                                                                (new TextField('Data[Division]', 'Klasse', 'Klasse')),
                                                            )), 4),
                                                    ))
                                                )
                                                , new Primary('Vorschau erstellen'),
                                                '/Education/Graduation/Certificate/Create',
                                                array('Template' => $Template))
                                        )
                                    ), new Title('Verfügbare Daten-Felder')),
                                ))
                            );
                        } else {
                            // TODO: Error
                        }
                    } else {
                        // TODO: Error
                    }
                } else {
                    // TODO: Error
                }
            } else {
                // TODO: Error
            }
        } else {
            // TODO: Error
        }
        return $Stage;
    }

    public function frontendCreate($Data, $Content = null)
    {

        // TODO: Find Template in Database (DMS)
        $this->getCache(new TwigHandler())->clearCache();

        $Header = (new Slice())
            ->addSection(
                (new Section())
                    ->addColumn(
                        (new Element())
                            ->setContent('MS Jahreszeugnis 3c.pdf')
                            ->styleTextSize('12px')
                            ->styleTextColor('#CCC')
                            ->styleAlignCenter()
                        , '25%'
                    )->addColumn(
                        (new Element\Sample())
                            ->styleTextSize('30px')
                    )->addColumn(
                        (new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg', '200px')), '25%'
                    )
            );

        $Content = (new Frame())->addDocument(
            (new Document())
                ->addPage(
                    (new Page())
                        ->addSlice(
                            $Header
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Name der Schule:')
                                            , '18%'
                                        )
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('{{ Data.School.Name }}')
                                                ->styleBorderBottom()
                                            , '82%'
                                        )
                                )
                        )
                        ->addSlice(
                            (new Slice())
                                ->addElement(
                                    (new Element())
                                        ->setContent('Jahreszeugnis der Mittelschule')
                                        ->styleTextSize('18px')
                                        ->styleTextBold()
                                        ->styleAlignCenter()
                                        ->styleMarginTop('10px')
                                )
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Klasse:')
                                            , '7%'
                                        )
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('{{ Data.Division }}')
                                                ->styleBorderBottom()
                                                ->styleAlignCenter()
                                            , '7%'
                                        )
                                        ->addColumn(
                                            (new Element())
                                            , '55%'
                                        )
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Schulhalbjahr:')
                                                ->styleAlignRight()
                                            , '18%'
                                        )
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('2015/16')
                                                ->styleBorderBottom()
                                                ->styleAlignCenter()
                                            , '13%'
                                        )
                                )->styleMarginTop('20px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Vorname und Name:')
                                            , '21%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('{{ Data.Name }}')
                                                ->styleBorderBottom()
                                            , '79%')
                                )->styleMarginTop('5px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addElement(
                                    (new Element())
                                        ->setContent('nahm am Unterricht mit dem Ziel des
                                Hauptschulabschlusses/Realschulabschlusses¹ teil.²')
                                        ->styleTextSize('11px')
                                        ->styleMarginTop('7px')
                                )->styleMarginTop('5px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Betragen')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                        ->addColumn(
                                            (new Element())
                                            , '4%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Mitarbeit')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                )
                                ->styleMarginTop('7px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Fleiß')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                        ->addColumn(
                                            (new Element())
                                            , '4%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Ordnung')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                )
                                ->styleMarginTop('7px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Einschätzung:')
                                            , '16%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom('1px', '#BBB')
                                            , '84%')
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom('1px', '#BBB')
                                        )
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom('1px', '#BBB')
                                        )
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addElement(
                                    (new Element())
                                        ->setContent('Leistungen in den einzelnen Fächern:')
                                        ->styleMarginTop('7px')
                                        ->styleTextBold()
                                )
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Deutsch')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                        ->addColumn(
                                            (new Element())
                                            , '4%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Mathematik')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                )
                                ->styleMarginTop('7px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Englisch')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                        ->addColumn(
                                            (new Element())
                                            , '4%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Biologie')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                )
                                ->styleMarginTop('5px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Kunst')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                        ->addColumn(
                                            (new Element())
                                            , '4%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Chemie')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                )
                                ->styleMarginTop('5px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Musik')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                        ->addColumn(
                                            (new Element())
                                            , '4%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Physik')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                )
                                ->styleMarginTop('5px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Geschichte')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                        ->addColumn(
                                            (new Element())
                                            , '4%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Sport')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                )
                                ->styleMarginTop('5px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Gemeinschaftskunde/Rechtserziehung')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                        ->addColumn(
                                            (new Element())
                                            , '4%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('EV./Kath. Religion/Ethik¹')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                )
                                ->styleMarginTop('5px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Geographie')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                        ->addColumn(
                                            (new Element())
                                            , '4%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Technik/Computer')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                )
                                ->styleMarginTop('5px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Wirtschaft-Technick-Haushalt/Soziales')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                        ->addColumn(
                                            (new Element())
                                            , '4%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Informatik')
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                )
                                ->styleMarginTop('5px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->styleMarginTop('16px')
                                                ->styleBorderBottom()
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                        ->addColumn(
                                            (new Element())
                                            , '4%')
                                        ->addColumn(
                                            (new Element())
                                                ->styleMarginTop('16px')
                                                ->styleBorderBottom()
                                                ->stylePaddingTop()
                                            , '39%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                                ->stylePaddingTop()
                                                ->stylePaddingBottom()
                                            , '9%')
                                )
                                ->styleMarginTop('5px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addElement(
                                    (new Element())
                                        ->setContent('Wahlpflichtbereich:')
                                        ->styleMarginTop('15px')
                                        ->styleTextBold()
                                )
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom()
                                        )
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBackgroundColor('#BBB')
                                                ->styleBorderBottom('1px', '#000')
                                            , '9%')
                                )
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Neigungskurs (Neigungskursbereich)/2. Fremdsprache (abschlussorientiert)¹')
                                                ->styleTextSize('11px')
                                        )
                                )
                                ->styleMarginTop('15px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Bemerkungen:')
                                            , '16%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom('1px', '#BBB')
                                            , '4%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Fehltage entschuldigt:')
                                                ->styleBorderBottom('1px', '#BBB')
                                            , '30%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom('1px', '#BBB')
                                            , '10%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('unentschuldigt:')
                                                ->styleBorderBottom('1px', '#BBB')
                                            , '30%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom('1px', '#BBB')
                                            , '10%')
                                )
                                ->styleMarginTop('15px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom('1px', '#BBB')
                                        )
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom('1px', '#BBB')
                                        )
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Versetzungsvermerk:')
                                            , '22%'
                                        )
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleBorderBottom('1px', '#BBB')
                                            , '58%'
                                        )
                                        ->addColumn(
                                            (new Element())
                                            , '20%'
                                        )
                                )
                                ->styleMarginTop('10px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Datum:')
                                            , '7%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent(date('d.m.Y'))
                                                ->styleBorderBottom('1px', '#000')
                                                ->styleAlignCenter()
                                            , '23%')
                                        ->addColumn(
                                            (new Element())
                                            , '5%')
                                        ->addColumn(
                                            (new Element())
                                            , '30%')
                                        ->addColumn(
                                            (new Element())
                                            , '5%')
                                        ->addColumn(
                                            (new Element())
                                            , '30%')
                                )
                                ->styleMarginTop('30px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBorderBottom('1px', '#000')
                                            , '30%')
                                        ->addColumn(
                                            (new Element())
                                            , '40%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp;')
                                                ->styleAlignCenter()
                                                ->styleBorderBottom('1px', '#000')
                                            , '30%')
                                )
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Schulleiter(in)')
                                                ->styleAlignCenter()
                                                ->styleTextSize('11px')
                                            , '30%')
                                        ->addColumn(
                                            (new Element())
                                            , '5%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Dienstsiegel der Schule')
                                                ->styleAlignCenter()
                                                ->styleTextSize('11px')
                                            , '30%')
                                        ->addColumn(
                                            (new Element())
                                            , '5%')
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Klassenlehrer(in)')
                                                ->styleAlignCenter()
                                                ->styleTextSize('11px')
                                            , '30%')
                                )
                                ->styleMarginTop('25px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Zur Kenntnis genommen:')
                                            , '30%'
                                        )
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('&nbsp')
                                                ->styleBorderBottom()
                                            , '40px'
                                        )
                                        ->addColumn(
                                            (new Element())
                                            , '30%'
                                        )
                                )
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                            , '30%'
                                        )
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Eltern')
                                                ->styleAlignCenter()
                                                ->styleTextSize('11px')
                                            , '40px'
                                        )
                                        ->addColumn(
                                            (new Element())
                                            , '30%'
                                        )
                                )->styleMarginTop('25px')
                        )
                        ->addSlice(
                            (new Slice())
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->styleBorderBottom()
                                            , '30%'
                                        )
                                        ->addColumn(
                                            (new Element())
                                            , '70%'
                                        )
                                )->styleMarginTop('11px')
                                ->addSection(
                                    (new Section())
                                        ->addColumn(
                                            (new Element())
                                                ->setContent('Notenerläuterung:<br/>
                                                    1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft;
                                                    6 = ungenügend (6 = ungenügend nur bei der Bewertung der Leistungen)<br/>
                                                    ¹ &nbsp;&nbsp;&nbsp; Zutreffendes ist zu unterstreichen.<br/>
                                                    ² &nbsp;&nbsp;&nbsp; Gild nicht für Klassenstufen 5 und 6')
                                                ->styleTextSize('9.5px')
                                            , '30%')
                                )
                        )
                )
        );

        $Content->setData($Data);

        $Preview = $Content->getContent();

//        $FileLocation = Storage::useWriter()->getTemporary('pdf', 'Zeugnistest-'.date('Ymd-His'), true);
//        /** @var DomPdf $Document */
//        $Document = \MOC\V\Component\Document\Document::getPdfDocument($FileLocation->getFileLocation());
//        $Document->setContent($Content->getTemplate());
//        $Document->saveFile(new FileParameter($FileLocation->getFileLocation()));

        $Stage = new Stage();

        $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(array(
//                $FileLocation->getFileLocation(),
                '<div class="cleanslate">'.$Preview.'</div>'
            ), 12),
//            new LayoutColumn(array(
//                '<pre><code class="small">'.( str_replace("\n", " ~~~ ",
//                    file_get_contents($FileLocation->getFileLocation())) ).'</code></pre>'
//                FileSystem::getDownload($FileLocation->getRealPath(),
//                    "Zeugnis ".date("Y-m-d H:i:s").".pdf")->__toString()
//            ), 6),
        )))));

        return $Stage;
    }
}