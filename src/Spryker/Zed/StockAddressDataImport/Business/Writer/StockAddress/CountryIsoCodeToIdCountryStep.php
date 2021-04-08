<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\StockAddressDataImport\Business\Writer\StockAddress;

use Orm\Zed\Country\Persistence\Map\SpyCountryTableMap;
use Orm\Zed\Country\Persistence\SpyCountryQuery;
use Spryker\Zed\DataImport\Business\Exception\EntityNotFoundException;
use Spryker\Zed\DataImport\Business\Exception\InvalidDataException;
use Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepInterface;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface;
use Spryker\Zed\StockAddressDataImport\Business\Writer\StockAddress\DataSet\StockAddressDataSetInterface;

class CountryIsoCodeToIdCountryStep implements DataImportStepInterface
{
    /**
     * @var array
     */
    protected static $idCountryCache = [];

    /**
     * @param \Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface $dataSet
     *
     * @throws \Spryker\Zed\DataImport\Business\Exception\EntityNotFoundException
     * @throws \Spryker\Zed\DataImport\Business\Exception\InvalidDataException
     *
     * @return void
     */
    public function execute(DataSetInterface $dataSet)
    {
        $iso2Code = $dataSet[StockAddressDataSetInterface::COLUMN_COUNTRY_ISO2_CODE];

        if (!$iso2Code) {
            throw new InvalidDataException(sprintf('ISO2 code is missing.'));
        }

        $idCountry = $this->findIdCountryByIso2Code($iso2Code);
        if (!$idCountry) {
            throw new EntityNotFoundException(sprintf('Country with ISO2 code "%s" not found.', $iso2Code));
        }

        $dataSet[StockAddressDataSetInterface::ID_COUNTRY] = $idCountry;
    }

    /**
     * @param string $iso2Code
     *
     * @return int|null
     */
    protected function findIdCountryByIso2Code(string $iso2Code): ?int
    {
        if (array_key_exists($iso2Code, static::$idCountryCache)) {
            return static::$idCountryCache[$iso2Code];
        }

        /** @var int|null $idCountry */
        $idCountry = SpyCountryQuery::create()
            ->filterByIso2Code($iso2Code)
            ->select(SpyCountryTableMap::COL_ID_COUNTRY)
            ->findOne();

        static::$idCountryCache[$iso2Code] = $idCountry;

        return static::$idCountryCache[$iso2Code];
    }
}
