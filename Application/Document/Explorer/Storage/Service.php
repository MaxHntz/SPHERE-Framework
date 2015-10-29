<?php
namespace SPHERE\Application\Document\Explorer\Storage;

use SPHERE\Application\Document\Explorer\Storage\Service\Data;
use SPHERE\Application\Document\Explorer\Storage\Service\Entity\TblFile;
use SPHERE\Application\Document\Explorer\Storage\Service\Setup;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Document\Explorer\Storage
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        return (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
    }

    /**
     * @param int $Id
     *
     * @return false|TblFile
     */
    public function getFileById($Id)
    {

        return (new Data($this->getBinding()))->getFileById($Id);
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param string $FileName
     * @param string $FileExtension
     * @param string $FileContent
     * @param string $FileType
     * @param int    $FileSize
     *
     * @return TblFile
     */
    public function insertFile($Name, $Description, $FileName, $FileExtension, $FileContent, $FileType, $FileSize)
    {

        return (new Data($this->getBinding()))->createFile(
            $Name, $Description, $FileName, $FileExtension, $FileContent, $FileType, $FileSize
        );
    }
}
