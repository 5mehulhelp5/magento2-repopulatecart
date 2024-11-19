<?php
/**
 * Sku
 *
 * @copyright Copyright © 2024 Blackbird. All rights reserved.
 * @author    emilie (Blackbird Team)
 */
declare(strict_types=1);


namespace Blackbird\RepopulateCart\Controller\Add;


use Blackbird\RepopulateCart\Api\RepopulateServiceInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;

class Sku implements HttpGetActionInterface
{

    protected const SKU_QUERY_PARAM = 'sku';

    public function __construct(
        protected readonly RequestInterface $request,
        protected RedirectInterface $redirect,
        protected ResponseInterface $response,
        protected RepopulateServiceInterface $populateServiceInterface,
        protected ManagerInterface $messageManager
    ) {
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute(): ResponseInterface
    {
        $sku = $this->request->getParam(self::SKU_QUERY_PARAM);
        if (!$sku) {
            $this->messageManager->addErrorMessage(__('No sku provided to repopulate cart.'));
            return $this->redirect('/');
        }
        try {
            return $this->redirect($this->populateServiceInterface->executeFromSku($sku));
        } catch (NoSuchEntityException) {
            $this->messageManager->addErrorMessage(__('Product not found.'));
        } catch (\Exception $e) {
        $e->getMessage();
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
