<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\EventListener;

use Mautic\EmailBundle\Model\EmailModel;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\MauticCitrixBundle\Entity\CitrixEventTypes;
use MauticPlugin\MauticCitrixBundle\Helper\CitrixHelper;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

trait CitrixStartTrait
{
    public function startProduct(string $product, Lead $lead, array $productsToStart, mixed $emailId = null, ?string $actionId = null)
    {
        $leadFields = $lead->getProfileFields();
        [$email, $firstname, $lastname] = [
            $leadFields['email'] ?? '',
            $leadFields['firstname'] ?? '',
            $leadFields['lastname'] ?? '',
        ];

        if ('' !== $email && '' !== $firstname && '' !== $lastname) {
            foreach ($productsToStart as $productToStart) {
                $productId = $productToStart['productId'];

                //$hostUrl = CitrixHelper::startToProduct(
                $hostUrl = $this->serviceHelper->startToProduct(
                    $product,
                    $productId,
                    $email,
                    $firstname,
                    $lastname
                );

                if ('' !== $hostUrl) {
                    // send email using template from form action properties
                    // and replace the tokens in the body with the hostUrl
                    $emailEntity = $this->emailModel->getEntity($emailId);

                    // make sure the email still exists and is published
                    if (null !== $emailEntity && $emailEntity->isPublished()) {
                        $content = $emailEntity->getCustomHtml();
                        // replace tokens
                        if ($this->serviceHelper->isIntegrationAuthorized($product)) {
                            $params = [
                                'product' => $product,
                                'productLink' => $hostUrl,
                                'productText' => sprintf($this->translator->trans('plugin.citrix.start.producttext'), ucfirst($product)),
                            ];

                            $button = $this->templating->render(
                                '@MauticCitrix/SubscribedEvents/EmailToken/token.html.twig',
                                $params
                            );
                            $content = str_replace('{' . $product . '_button}', $button, $content);
                        } else {
                            // remove the token
                            $content = str_replace('{' . $product . '_button}', '', $content);
                        }

                        // set up email data
                        $emailEntity->setCustomHtml($content);
                        $leadFields['id'] = $lead->getId();
                        $options = ['source' => ['trigger', $actionId]];
                        $this->emailModel->sendEmail($emailEntity, $leadFields, $options);
                    } else {
                        throw new BadRequestHttpException('Unable to load email template!');
                    }

                    // add event to DB
                    $eventName = CitrixHelper::getCleanString(
                            $productToStart['productTitle']
                        ) . '_#' . $productToStart['productId'];

                    $this->citrixModel->addEvent(
                        $product,
                        $email,
                        $eventName,
                        $productToStart['productTitle'],
                        CitrixEventTypes::STARTED,
                        $lead
                    );
                } else {
                    throw new BadRequestHttpException('Unable to start!');
                }
            }
        } else {
            throw new BadRequestHttpException('Mandatory lead fields not found!');
        }
    }
}
