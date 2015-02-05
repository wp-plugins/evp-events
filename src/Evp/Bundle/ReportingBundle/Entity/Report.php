<?php
/**
 * Represents the Report Entity
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\ReportingBundle\Entity;

/**
 * Class Report
 */
class Report
{
    /**
     * @var mixed
     */
    protected $cols;

    /**
     * @var mixed
     */
    protected $rows;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var mixed
     */
    protected $totals;

    /**
     * @var mixed
     */
    protected $totalsRows;

    /**
     * @param mixed $cols
     * @return Report
     */
    public function setCols($cols)
    {
        $this->cols = $cols;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCols()
    {
        return $this->cols;
    }

    /**
     * @param mixed $data
     * @return Report
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $rows
     * @return Report
     */
    public function setRows($rows)
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @param mixed $totals
     * @return Report
     */
    public function setTotals($totals)
    {
        $this->totals = $totals;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotals()
    {
        return $this->totals;
    }

    /**
     * @param mixed $totalsRows
     * @return Report
     */
    public function setTotalsRows($totalsRows)
    {
        $this->totalsRows = $totalsRows;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotalsRows()
    {
        return $this->totalsRows;
    }
}
