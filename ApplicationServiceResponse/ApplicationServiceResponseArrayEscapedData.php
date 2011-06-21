<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceResponse;

class ApplicationServiceResponseArrayEscapedData extends ApplicationServiceResponseArray
{
    public function getResponse()
    {
        $data = parent::getResponse();
        
        return $this->escapeData($data);
    }
    
    public function escapeData($data)
    {
        if (isset($data['row'])) {
            $data['row'] = $this->escapeDataInArray($data['row']);
        }
        
        if (isset($data['rows'])) {
            $data['rows'] = $this->escapeDataInArray($data['rows']);
        }
        
        return $data;
    }
    
    public function escapeDataInArray($array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->escapeDataInArray($value);
            } else if (!is_numeric($value)) {
                $array[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
        
        return $array;
    }
}
