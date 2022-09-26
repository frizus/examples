<?php
namespace Frizus\Module\Repository;

class RecommendSectionsRepository
{
    public $lastVariant;

    protected $sectionId;

    protected $sectionExists;

    protected $parentSectionId;

    protected $baseFilter;

    public function __construct($sectionId, $iblockId)
    {
        $this->sectionId = intval($sectionId);
        $this->baseFilter = [
            'ACTIVE' => 'Y',
            'GLOBAL_ACTIVE' => 'Y',
            'IBLOCK_ID' => $iblockId,
            'IBLOCK_ACTIVE' => 'Y',
            'CHECK_PERMISSIONS' => 'Y',
            'MIN_PERMISSION' => 'R',
        ];
        $this->initSection();
    }

    public function isLastVariant()
    {
        return $this->lastVariant === 0;
    }

    public function getVariantOfIds()
    {
        if (!isset($this->lastVariant)) {
            if ($this->sectionExists) {
                if (!($this->parentSectionId > 0)) {
                    $otherRootSections = $this->getOtherRootSections();
                    if (!$otherRootSections) {
//                        $this->lastVariant = 0;
//                        return $this->getNoSections();
                        $this->lastVariant = 1;
                        return $this->getThisSection();
                    } else {
                        $this->lastVariant = 2;
                        return $otherRootSections;
                    }
                } else {
                    $siblingSections = $this->getSiblingSections();
                    if (!$siblingSections) {
//                        $this->lastVariant = 0;
//                        return $this->getNoSections();
                        $this->lastVariant = 1;
                        return $this->getThisParentSection();
                    } else {
                        $this->lastVariant = 2;
                        return $siblingSections;
                    }
                }
            } else {
                $this->lastVariant = 0;
                return $this->getNoSections();
            }
        } elseif ($this->lastVariant === 2) {
//            $this->lastVariant = 0;
//            return $this->getNoSections();
            $this->lastVariant = 1;
            return $this->getThisSection();
        } elseif ($this->lastVariant === 1) {
            $this->lastVariant = 0;
            return $this->getNoSections();
        }
    }

    protected function getNoSections()
    {
        return [];
    }

    protected function getThisParentSection()
    {
        return [$this->parentSectionId];
    }

    protected function getThisSection()
    {
        return [$this->sectionId];
    }

    protected function getSiblingSections()
    {
        $filter = array_merge($this->baseFilter, [
            'SECTION_ID' => $this->parentSectionId,
            '!=ID' => $this->sectionId,
        ]);
        $result = \CIBlockSection::GetList([], $filter, false, ['ID'], false);
        $sectionIds = [];
        while ($row = $result->Fetch()) {
            $sectionIds[] = intval($row['ID']);
        }
        return !empty($sectionIds) ? $sectionIds : false;
    }

    protected function getOtherRootSections()
    {
        $filter = array_merge($this->baseFilter, [
            'DEPTH_LEVEL' => 1,
            '!=ID' => $this->sectionId,
        ]);
        $result = \CIBlockSection::GetList([], $filter, false, ['ID'], false);
        $sectionIds = [];
        while ($row = $result->Fetch()) {
            $sectionIds[] = intval($row['ID']);
        }
        return !empty($sectionIds) ? $sectionIds : false;
    }

    protected function initSection()
    {
        $filter = array_merge($this->baseFilter, [
            'ID' => $this->sectionId,
        ]);
        $result = \CIBlockSection::GetList([], $filter, false, ['ID', 'IBLOCK_SECTION_ID'], false);
        $section = $result->Fetch();
        $this->sectionExists = (bool)$section;
        if ($this->sectionExists) {
            $this->parentSectionId = intval($section['IBLOCK_SECTION_ID']);
        }
    }
}
