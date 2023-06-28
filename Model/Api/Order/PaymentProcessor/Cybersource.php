<?php

namespace Riskified\Decider\Model\Api\Order\PaymentProcessor;

class Cybersource extends AbstractPayment
{
    /**
     * @return array
     */
    public function getDetails()
    {
        $details = [];
        $details['transaction_id'] = $this->payment->getAdditionalInformation('tokenbase_id');
        $details['credit_card_bin'] = $this->payment->getAdditionalInformation('cc_bin');
        $details['avs_result_code'] = $this->payment->getData('ccAuthReply.avsCode');
        $details['cvv_result_code'] = $this->payment->getData('ccAuthReply.cvCode');
        $details['credit_card_number'] = $this->payment->getData('cc_last_4');
        $details['credit_card_company'] = $this->payment->getData('cc_type');

        return $details;
    }
}
