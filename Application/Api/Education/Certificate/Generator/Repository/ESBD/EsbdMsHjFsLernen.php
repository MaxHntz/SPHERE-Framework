<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESBD;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class EsbdMsHjFsLernen
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESBD
 */
class EsbdMsHjFsLernen  extends EsbdStyle
{

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $pageList[] = $this->getPageOne($personId);
        $pageList[] = $this->getPageTwo($personId);

        return $pageList;
    }

    /**
     * @param $personId
     *
     * @return Page
     */
    public function getPageOne($personId)
    {

        return (new Page())
            ->addSlice($this->getHeadConsumer('Evangelisches Schulzentrum Bad Düben - Oberschule'))
            ->addSlice($this->getCertificateHeadConsumer('Halbjahreszeugnis der Oberschule', '5px'))
            ->addSlice($this->getDivisionAndYearConsumer($personId, '20px', '1. Schulhalbjahr'))
            ->addSlice($this->getStudentNameConsumer($personId))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('nahm am Unterricht mit dem Ziel des Abschlusses im Förderschwerpunkt Lernen teil.')
                    ->styleMarginTop('5px')
                    ->styleHeight('18px')
                )
            )
            ->addSlice($this->getGradeLanes($personId))
            ->addSlice($this->getGradeInfo())
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleMarginTop('15px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getSubjectLanes(
                $personId,
                true,
                array(),
                '14px',
                false,
                false,
                true
            )->styleHeight('290px'))
            ->addSlice($this->getDescriptionConsumer($personId, '100px', '15px'))
            ->addSlice($this->getMissingConsumer($personId))
            ->addSlice($this->getDateLineConsumer($personId))
            ->addSlice($this->getSignPartConsumer($personId))
            ->addSlice($this->getParentSignConsumer())
            ->addSlice($this->getInfoConsumer('30px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend 
                    (6 = ungenügend nur bei der Bewertung der Leistungen)',
                '¹ &nbsp;&nbsp;&nbsp; gemäß § 27 Absatz 6 der Schulordnung Ober- und Abendoberschulen'))
            ->addSlice($this->getBottomLineConsumer());
    }

    /**
     * @param $personId
     *
     * @return Page
     */
    public function getPageTwo($personId)
    {

        return (new Page())
            ->addSlice($this->getHeadConsumer('Evangelisches Schulzentrum Bad Düben - Oberschule'))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('DIALOGUS')
                    ->styleTextSize('28pt')
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleMarginTop('5px')
                )
            )
            ->addSlice($this->getDivisionAndYearConsumer($personId, '10px', '1. Schulhalbjahr'))
            ->addSlice($this->getStudentNameConsumer($personId))
            ->addSliceArray($this->getSecondPageDescription($personId))
            ->addSlice($this->getBottomLineConsumer('42px'));
    }
}