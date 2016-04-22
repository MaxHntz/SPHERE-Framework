<?php
namespace SPHERE\Application\Api\Education\Graduation\Certificate\Repository;

use SPHERE\Application\Api\Education\Graduation\Certificate\Certificate;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Document;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Element;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Frame;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Page;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Section;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Slice;
use SPHERE\Common\Frontend\Layout\Repository\Container;

/**
 * Class CheBeMi
 *
 * @package SPHERE\Application\Api\Education\Graduation\Certificate\Repository
 */
class CheBeMi extends Certificate
{

    /**
     * @param bool $IsSample
     *
     * @return Frame
     */
    public function buildCertificate($IsSample = true)
    {

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schule')
                            ->stylePaddingTop('5px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderTop()
                            ->styleBorderRight()
                            ->styleBorderLeft()
                            ->styleTextSize('9px')
                            , '50%')
                        ->addElementColumn((new Element())
                            , '50%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Company.Data.Name }}'
                                .new Container('{{ Content.Company.Address.Street.Name }}
                                                {{ Content.Company.Address.Street.Number }}')
                                .new Container('{{ Content.Company.Address.City.Code }}
                                                {{ Content.Company.Address.City.Name }}'))
                            ->stylePaddingBottom('5px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->styleBorderLeft()
                            , '50%')
                        ->addElementColumn((new Element())
                            , '50%')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Bildungsempfehlung in den Klassenstufen 5 und 6')
                        ->styleAlignCenter()
                        ->styleTextSize('25px')
                        ->styleTextBold()
                        ->styleMarginTop('10px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vor- und Zuname')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft()
                            ->styleBorderRight()
                            ->styleTextSize('9px')
                            , '70%')
                        ->addElementColumn((new Element())
                            ->setContent('Klasse')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft()
                            ->styleBorderRight()
                            ->styleTextSize('9px')
                            , '15%')
                        ->addElementColumn((new Element())
                            ->setContent('Schuljahr')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft()
                            ->styleTextSize('9px')
                            , '15%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Person.Data.Name.First }}
                                          {{ Content.Person.Data.Name.Last }}')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft('5px')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            , '70%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Division.Data.Level.Name }}{{ Content.Division.Data.Name }}')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft('5px')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            , '15%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Division.Data.Year }}')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft('5px')
                            ->styleBorderBottom()
                            , '15%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('geboren am')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft()
                            ->styleBorderRight()
                            ->styleTextSize('9px')
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('in')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft()
                            ->styleTextSize('9px')
                            , '70%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft('5px')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Person.Common.BirthDates.Birthplace is not empty) %}
                                    {{ Content.Person.Common.BirthDates.Birthplace }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->stylePaddingLeft('5px')
                            ->styleBorderBottom()
                            , '70%')
                    )
                    ->addElement((new Element())
                        ->setContent('Wohnhaft in')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->stylePaddingLeft()
                        ->styleTextSize('9px')
                    )
                    ->addElement((new Element())
                        ->setContent('{% if(Content.Person.Address.City.Name) %}
                                    {{ Content.Person.Address.Street.Name }}
                                    {{ Content.Person.Address.Street.Number }},
                                    {{ Content.Person.Address.City.Code }}
                                    {{ Content.Person.Address.City.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->stylePaddingLeft('5px')
                        ->styleBorderBottom()
                    )
                    ->addElement((new Element())
                        ->setContent('Name der Eltern')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->stylePaddingLeft()
                        ->styleTextSize('9px')
                    )
                    ->addElement((new Element())
                        ->setContent('{% if(Content.Person.Parent) %}
                                    {{ Content.Person.Parent.Mother.Name.First }}
                                    {{ Content.Person.Parent.Mother.Name.Last }},
                                    {{ Content.Person.Parent.Father.Name.First }}
                                    {{ Content.Person.Parent.Father.Name.Last }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
                        ->stylePaddingLeft('5px')
                    )
                    ->styleBorderTop()
                    ->styleBorderLeft()
                    ->styleBorderRight()
                    ->styleBorderBottom()
                    ->styleMarginTop('5px')
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('1. Leistungsstand')
                        ->styleTextSize('20px')
                        ->styleTextBold()
                        ->styleMarginTop('5px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->styleHeight('25px')
                            ->styleMarginTop('5px')
                            ->setContent('&nbsp;')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Deutsch/Sorbisch¹ ²')
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.DE is not empty) %}
                                    {{ Content.Grade.Data.DE }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            , '20%')
                        ->addElementColumn((new Element())
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('Mathematik')
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.MA is not empty) %}
                                    {{ Content.Grade.Data.MA }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            , '20%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Englisch')
                            ->stylePaddingTop('20px')
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.EN is not empty) %}
                                    {{ Content.Grade.Data.EN }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->stylePaddingTop('20px')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->stylePaddingTop('20px')
                            , '55%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Durchschnitt der Noten aus den angegebenen Fächern')
                            ->stylePaddingTop('20px')
                            , '80%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.DE is not empty) %}
                                    {{ ((Content.Grade.Data.DE + Content.Grade.Data.MA + Content.Grade.Data.EN) / 3)|round(2, "floor") }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->stylePaddingTop('20px')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            , '20%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '80%')
                        ->addElementColumn((new Element())
                            ->stylePaddingTop()
                            ->setContent('(in Ziffern)')
                            ->styleTextSize('9px')
                            , '20%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Durchschnitt der Noten in allen anderen Fächern')
                            ->stylePaddingTop('10px')
                            , '80%')
                        ->addElementColumn((new Element())
                            ->setContent('---')
                            ->stylePaddingTop('20px')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            , '20%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '80%')
                        ->addElementColumn((new Element())
                            ->stylePaddingTop()
                            ->setContent('(in Ziffern)')
                            ->styleTextSize('9px')
                            , '20%')
                    )
                    ->stylePaddingLeft('5px')
                    ->stylePaddingRight('5px')
                    ->styleBorderTop()
                    ->styleBorderBottom()
                    ->styleBorderLeft()
                    ->styleBorderRight()

                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('2. Gutachten³')
                        ->styleTextSize('20px')
                        ->styleTextBold()
                        ->styleMarginTop('5px')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('{% if(Content.Input.Survey is not empty) %}
                                    {{ Content.Input.Survey|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleHeight('200px')
                        ->stylePaddingTop()
                        ->stylePaddingLeft('5px')
                        ->stylePaddingRight('5px')
                        ->stylePaddingBottom()
                        ->styleBorderTop()
                        ->styleBorderLeft()
                        ->styleBorderRight()
                        ->styleBorderBottom()
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Auf Grund des Leitungsstandes und des Gutachtens wird 
                        {% if Content.Person.Common.BirthDates.Gender == 2 %}
                                der Schülerin
                            {% else %}
                                {% if Content.Person.Common.BirthDates.Gender == 1 %}
                                    dem Schüler
                                {% else %}
                                    Schülerin/dem Schüler¹
                                {% endif %}
                            {% endif %} empfohlen:')
                        ->styleMarginTop('15px')
                    )
                    ->addElement((new Element())
                        ->setContent('{% if Content.Person.Common.BirthDates.Gender == 2 %}
                                Die Schülerin setzt ihre Ausbildung an der Mittelschule fort.
                            {% else %}
                                {% if Content.Person.Common.BirthDates.Gender == 1 %}
                                    Der Schüler setzt seine Ausbildung an der Mittelschule fort.
                                {% else %}
                                    Die Schülerin/Der Schüler¹ setzt ihre/seine¹ Ausbildung an der Mittelschule fort.
                                {% endif %}
                            {% endif %}')
                        ->styleMarginTop('21px')
                    )
                    ->styleHeight('85px')
                    ->stylePaddingTop()
                    ->stylePaddingLeft('5px')
                    ->stylePaddingRight('5px')
                    ->stylePaddingBottom()
                    ->styleBorderTop()
                    ->styleBorderLeft()
                    ->styleBorderRight()
                    ->styleBorderBottom()
                    ->styleMarginTop('20px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Date is not empty) %}
                                    {{ Content.Input.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleBorderBottom('1px', '#000')
                            ->styleAlignCenter()
                            ->styleMarginTop('10px')

                            , '23%')
                        ->addElementColumn((new Element())
                            , '77%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Datum')
                            ->styleTextSize('9px')
                            ->stylePaddingTop()
                            ->stylePaddingBottom('20px')

                            , '23%')
                        ->addElementColumn((new Element())
                            , '77%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->styleBorderBottom('1px', '#000')
                            ->styleAlignCenter()
                            ->styleMarginTop('20px')

                            , '40%')
                        ->addElementColumn((new Element())
                            ->setContent('Dienstsiegel'
                                .new Container('der Schule'))
                            ->styleTextSize('9px')
                            ->styleAlignCenter()
                            , '20%')
                        ->addElementColumn((new Element())
                            ->styleBorderBottom('1px', '#000')
                            ->styleAlignCenter()
                            ->styleMarginTop('20px')
                            , '40%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schulleiter/in')
                            ->styleTextSize('9px')
                            ->stylePaddingTop()
                            , '40%')
                        ->addElementColumn((new Element())
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('Klassenlehrer/in')
                            ->styleTextSize('9px')
                            ->stylePaddingTop()
                            , '40%')
                    )
                    ->styleHeight('100px')
                    ->stylePaddingTop()
                    ->stylePaddingLeft('5px')
                    ->stylePaddingRight('5px')
                    ->stylePaddingBottom()
                    ->styleBorderTop()
                    ->styleBorderLeft()
                    ->styleBorderRight()
                    ->styleBorderBottom()
                    ->styleMarginTop('20px')
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('¹ Nichtzutreffendes streichen.'
                            .new Container('² sorbische Schulen, an denen Sorbisch je nach Unterrichtsfach und Klassenstufe
                            Unterrichtssprache ist, kann nach Entscheidung der Schulkonferenz gem. § 10 Abs. 6 SOMIA das
                            Fach Deutsch durch das Fach Sorbisch ersetzt werden.')
                            .new Container('³ Falls der Raum für Eintragungen nicht ausreicht, ist ein Beiblatt zu verwenden.')
                        )
                        ->styleTextSize('9px')
                        ->styleMarginTop('5px')
                    )
                )
            )
        );
    }
}