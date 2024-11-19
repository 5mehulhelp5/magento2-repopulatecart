<?php
/**
 * Index
 *
 * @copyright Copyright © 2024 Blackbird. All rights reserved.
 * @author    emilie (Blackbird Team)
 */
declare(strict_types=1);

namespace Blackbird\RepopulateCart\Controller\Index;

use Blackbird\RepopulateCart\Api\RepopulateServiceInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Url\DecoderInterface;

/**
 *
 */
class Index implements HttpGetActionInterface
{
    public function __construct(
        protected readonly RequestInterface $request,
        protected RedirectInterface $redirect,
        protected ResponseInterface $response,
        protected RepopulateServiceInterface $populateServiceInterface,
        protected DecoderInterface $urlDecoder,
        protected ManagerInterface $messageManager,
        protected SerializerInterface $serializer
    ) {

    }

    /*
     * URL Format
     * repopulate/index/index/itemids/3584;23698/qties/1;3
     * where itemids are product ids,
     * qties are quantities and
     * redir is the product id on wich the customer has clicked on his email
     *
     * /!\ Products should be simples otherwise products wouldn't be added
     * /!\ Si le nombre de qties entrées est inférieur au nombre d’item, les derniers seront ajoutés avec une quantité de 1
     */
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $params = $this->request->getParams();
        if (!$params) {
            $this->messageManager->addErrorMessage(__('No data provided to repopulate cart.'));
            return $this->redirect('/');
        }

        if (!array_key_exists('itemIds', $params) && isset($params['b64'])) {
            $params = $this->serializer->unserialize($this->urlDecoder->decode($params['b64']));
            if (isset($params['itemIds'], $params['qties'])) {
                $items = $params['itemIds'];
                $qties = $params['qties'];
            }
        } else {
            if (isset($params['itemIds'], $params['qties'])) {
                $items = explode(';', $params['itemIds']);
                $qties = explode(';', $params['qties']);
            }
        }

        if (!empty($items) && !empty($qties)) {
            $products = [];
            foreach ($items as $key => $value) {
                if (isset($products[$value])) {
                    $products[$value] += $qties[$key] ?? 1;
                } else {
                    $products[$value] = $qties[$key] ?? 1;
                }
            }

            return $this->redirect($this->populateServiceInterface->execute($products));
        }

        return $this->redirect('/');
    }

    /**
     * Set redirect into response
     *
     * @param string $path
     * @param array $arguments
     * @return ResponseInterface
     */
    protected function redirect(string $path, array $arguments = []): ResponseInterface
    {
        $this->redirect->redirect($this->response, $path, $arguments);
        return $this->response;
    }
}
