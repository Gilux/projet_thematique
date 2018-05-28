<?php

namespace DepotBundle\Utility;

class GroupeRenduUtility {

    /**
     * Retourne le minimum et le maximum de groupes disponibles en fonction du min,max d'étudiants par groupe de rendu
     * et le nombre d'étudiants concernés.
     * @param int $min_per_group
     * @param int $max_per_group
     * @param int $total_students
     * @return array Le minimum et le maximum de groupes
     */
    public function getMinMaxGroups($min_per_group, $max_per_group, $total_students) {
        $min_groups = 0;
        $max_groups = 0;

        for($i = 0, $j = 0; $i <= $total_students; $i+=$max_per_group, $j++) {
            $min_groups = $j;
        }
        if( ($min_groups * $max_per_group) - $total_students < 0) {
            $min_groups++;
        }

        for($i = 0, $j = 0; $i <= $total_students; $i+=$min_per_group, $j++) {
            $max_groups = $j;
        }

        return array("min" => $min_groups, "max" => $max_groups);
    }
}