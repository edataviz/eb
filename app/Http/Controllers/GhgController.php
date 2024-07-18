<?php

namespace App\Http\Controllers;

class GhgController extends CodeController
{
    public function __construct() {
        parent::__construct();
    }

    // EMISSION ENTRY
    // Combustion
    public function combustionEmissionEntry()
    {
        $filterGroups = array(
            'productionFilterGroup' => [],
            'dateFilterGroup' => array(
                ['id' => 'date_begin', 'name' => 'From date'],
                ['id' => 'date_end', 'name' => 'To date'],
            ),
            'frequenceFilterGroup'	=> ['EquipmentGroup','CodeEquipmentType']
        );
        return view('front.emission.combustion_emission_entry', ['filters' => $filterGroups]);
    }

    // Indirect
    public function indirectEmissionEntry()
    {
        $filterGroups = array(
            'productionFilterGroup' => [],
            'dateFilterGroup' => array(
                ['id' => 'date_begin', 'name' => 'From date'],
                ['id' => 'date_end', 'name' => 'To date'],
            ),
            'frequenceFilterGroup' => ['CodeReadingFrequency','CodeFlowPhase']
        );
        return view('front.emission.indirect_emission_entry', ['filters' => $filterGroups]);
    }

    // Events
    public function eventsEmissionEntry()
    {
        $filterGroups = array(
            'productionFilterGroup' => [],
            'dateFilterGroup' => array(
                ['id' => 'date_begin', 'name' => 'From date'],
                ['id' => 'date_end', 'name' => 'To date'],
            )
        );
        return view('front.emission.events_emission_entry', ['filters' => $filterGroups]);
    }

    // EMISSION SOURCES
    // Combustion
    public function combustionEmissionSources()
    {
        $filterGroups = array(
            'productionFilterGroup' => [],
            'dateFilterGroup' => array(
                ['id' => 'date_begin', 'name' => 'From date'],
                ['id' => 'date_end', 'name' => 'To date'],
            ),
            'frequenceFilterGroup'	=> ['EquipmentGroup','CodeEquipmentType']
        );
        return view('front.emission.combustion_emission_sources', ['filters' => $filterGroups]);
    }

    // Indirect
    public function indirectEmissionSources()
    {
        $filterGroups = array(
            'productionFilterGroup' => [],
            'dateFilterGroup' => array(
                ['id' => 'date_begin', 'name' => 'From date'],
                ['id' => 'date_end', 'name' => 'To date'],
            ),
            'frequenceFilterGroup' => ['CodeReadingFrequency','CodeFlowPhase']
        );
        return view('front.emission.indirect_emission_sources', ['filters' => $filterGroups]);
    }

    // Events
    public function eventsEmissionSources()
    {
        $filterGroups = array(
            'productionFilterGroup' => [],
            'dateFilterGroup' => array(
                ['id' => 'date_begin', 'name' => 'From date'],
                ['id' => 'date_end', 'name' => 'To date'],
            )
        );
        return view('front.emission.events_emission_sources', ['filters' => $filterGroups]);
    }

    // EMISSION RELEASE
    // Combustion
    public function combustionEmissionRelease()
    {
        $filterGroups = array(
            'productionFilterGroup' => [],
            'dateFilterGroup' => array(
                ['id' => 'date_begin', 'name' => 'From date'],
                ['id' => 'date_end', 'name' => 'To date'],
            ),
            'frequenceFilterGroup'	=> ['EquipmentGroup','CodeEquipmentType']
        );
        return view('front.emission.combustion_emission_release', ['filters' => $filterGroups]);
    }

    // Indirect
    public function indirectEmissionRelease()
    {
        $filterGroups = array(
            'productionFilterGroup' => [],
            'dateFilterGroup' => array(
                ['id' => 'date_begin', 'name' => 'From date'],
                ['id' => 'date_end', 'name' => 'To date'],
            ),
            'frequenceFilterGroup' => ['CodeReadingFrequency','CodeFlowPhase']
        );
        return view('front.emission.indirect_emission_release', ['filters' => $filterGroups]);
    }

    // Events
    public function eventsEmissionRelease()
    {
        $filterGroups = array(
            'productionFilterGroup' => [],
            'dateFilterGroup' => array(
                ['id' => 'date_begin', 'name' => 'From date'],
                ['id' => 'date_end', 'name' => 'To date'],
            )
        );
        return view('front.emission.events_emission_release', ['filters' => $filterGroups]);
    }
}