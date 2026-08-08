<?php

namespace App\Enums;

enum SdgCategory: int
{
    case NoPoverty = 1;
    case ZeroHunger = 2;
    case GoodHealthAndWellBeing = 3;
    case QualityEducation = 4;
    case GenderEquality = 5;
    case CleanWaterAndSanitation = 6;
    case AffordableAndCleanEnergy = 7;
    case DecentWorkAndEconomicGrowth = 8;
    case IndustryInnovationAndInfrastructure = 9;
    case ReducedInequalities = 10;
    case SustainableCitiesAndCommunities = 11;
    case ResponsibleConsumptionAndProduction = 12;
    case ClimateAction = 13;
    case LifeBelowWater = 14;
    case LifeOnLand = 15;
    case PeaceJusticeAndStrongInstitutions = 16;
    case PartnershipsForTheGoals = 17;

    public function label(): string
    {
        return match ($this) {
            self::NoPoverty => '1. No Poverty',
            self::ZeroHunger => '2. Zero Hunger',
            self::GoodHealthAndWellBeing => '3. Good Health and Well-being',
            self::QualityEducation => '4. Quality Education',
            self::GenderEquality => '5. Gender Equality',
            self::CleanWaterAndSanitation => '6. Clean Water and Sanitation',
            self::AffordableAndCleanEnergy => '7. Affordable and Clean Energy',
            self::DecentWorkAndEconomicGrowth => '8. Decent Work and Economic Growth',
            self::IndustryInnovationAndInfrastructure => '9. Industry, Innovation and Infrastructure',
            self::ReducedInequalities => '10. Reduced Inequalities',
            self::SustainableCitiesAndCommunities => '11. Sustainable Cities and Communities',
            self::ResponsibleConsumptionAndProduction => '12. Responsible Consumption and Production',
            self::ClimateAction => '13. Climate Action',
            self::LifeBelowWater => '14. Life Below Water',
            self::LifeOnLand => '15. Life on Land',
            self::PeaceJusticeAndStrongInstitutions => '16. Peace, Justice and Strong Institutions',
            self::PartnershipsForTheGoals => '17. Partnerships for the Goals',
        };
    }
}
