<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle;

final class CitrixEvents
{
    /**
     * The mautic.on_citrix_form_validate_action event is dispatched when a form is validated.
     *
     * The event listener receives a Mautic\FormBundle\Event\ValidationEvent instance.
     *
     * @var string
     */
    public const ON_FORM_VALIDATE_ACTION = 'mautic.on_citrix_form_validate_action';

    /**
     * The mautic.on_citrix_webinar_event event is dispatched when a campaign event is triggered.
     *
     * The event listener receives a Mautic\CampaignBundle\Event\CampaignExecutionEvent instance.
     *
     * @var string
     */
    public const ON_CITRIX_WEBINAR_EVENT = 'mautic.on_citrix_webinar_event';

    /**
     * The mautic.on_citrix_meeting_event event is dispatched when a campaign event is triggered.
     *
     * The event listener receives a Mautic\CampaignBundle\Event\CampaignExecutionEvent instance.
     *
     * @var string
     */
    public const ON_CITRIX_MEETING_EVENT = 'mautic.on_citrix_meeting_event';

    /**
     * The mautic.on_citrix_training_event event is dispatched when a campaign event is triggered.
     *
     * The event listener receives a Mautic\CampaignBundle\Event\CampaignExecutionEvent instance.
     *
     * @var string
     */
    public const ON_CITRIX_TRAINING_EVENT = 'mautic.on_citrix_training_event';

    /**
     * The mautic.on_citrix_assist_event event is dispatched when a campaign event is triggered.
     *
     * The event listener receives a Mautic\CampaignBundle\Event\CampaignExecutionEvent instance.
     *
     * @var string
     */
    public const ON_CITRIX_ASSIST_EVENT = 'mautic.on_citrix_assist_event';

    /**
     * The mautic.on_citrix_webinar_action event is dispatched when a campaign event is triggered.
     *
     * The event listener receives a Mautic\CampaignBundle\Event\CampaignExecutionEvent instance.
     *
     * @var string
     */
    public const ON_CITRIX_WEBINAR_ACTION = 'mautic.on_citrix_webinar_action';

    /**
     * The mautic.on_citrix_meeting_action event is dispatched when a campaign event is triggered.
     *
     * The event listener receives a Mautic\CampaignBundle\Event\CampaignExecutionEvent instance.
     *
     * @var string
     */
    public const ON_CITRIX_MEETING_ACTION = 'mautic.on_citrix_meeting_action';

    /**
     * The mautic.on_citrix_training_action event is dispatched when a campaign event is triggered.
     *
     * The event listener receives a Mautic\CampaignBundle\Event\CampaignExecutionEvent instance.
     *
     * @var string
     */
    public const ON_CITRIX_TRAINING_ACTION = 'mautic.on_citrix_training_action';

    /**
     * The mautic.on_citrix_assist_action event is dispatched when a campaign event is triggered.
     *
     * The event listener receives a Mautic\CampaignBundle\Event\CampaignExecutionEvent instance.
     *
     * @var string
     */
    public const ON_CITRIX_ASSIST_ACTION = 'mautic.on_citrix_assist_action';

    /**
     * The mautic.on_webinar_register_action event is dispatched when form with that action is submitted.
     *
     * The event listener receives a Mautic\CampaignBundle\Event\SubmissionEvent instance.
     *
     * @var string
     */
    public const ON_WEBINAR_REGISTER_ACTION = 'mautic.on_webinar_register_action';

    /**
     * The mautic.on_meeting_start_action event is dispatched when form with that action is submitted.
     *
     * The event listener receives a Mautic\CampaignBundle\Event\SubmissionEvent instance.
     *
     * @var string
     */
    public const ON_MEETING_START_ACTION = 'mautic.on_meeting_start_action';

    /**
     * The mautic.on_training_register_action event is dispatched when form with that action is submitted.
     *
     * The event listener receives a Mautic\CampaignBundle\Event\SubmissionEvent instance.
     *
     * @var string
     */
    public const ON_TRAINING_REGISTER_ACTION = 'mautic.on_training_register_action';

    /**
     * The mautic.on_training_start_action event is dispatched when form with that action is submitted.
     *
     * The event listener receives a Mautic\CampaignBundle\Event\SubmissionEvent instance.
     *
     * @var string
     */
    public const ON_TRAINING_START_ACTION = 'mautic.on_training_start_action';

    /**
     * The mautic.on_assist_remote_action event is dispatched when form with that action is submitted.
     *
     * The event listener receives a Mautic\CampaignBundle\Event\SubmissionEvent instance.
     *
     * @var string
     */
    public const ON_ASSIST_REMOTE_ACTION = 'mautic.on_assist_remote_action';

    /**
     * The mautic.on_citrix_token_generate event is dispatched before a token is decoded.
     *
     * The event listener receives a MauticPlugin\MauticCitrixBundle\Event\TokenGenerateEvent instance.
     *
     * @var string
     */
    public const ON_CITRIX_TOKEN_GENERATE = 'mautic.on_citrix_token_generate';

    /**
     * The mautic.on_citrix_event_update event is dispatched when an event has been updated externally.
     *
     * The event listener receives a MauticPlugin\MauticCitrixBundle\Event\CitrixEventUpdateEvent instance.
     *
     * @var string
     */
    public const ON_CITRIX_EVENT_UPDATE = 'mautic.on_citrix_event_update';
}
