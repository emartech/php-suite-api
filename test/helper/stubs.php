<?php return [
    '/internal/{customerId}/email/{campaignId}/' => '{
        "id": "1",
        "language": "en",
        "name": "20160407_1502_Copy of Test mail 1",
        "created": "2016-04-07 15:02:16",
        "deleted": "",
        "fromemail": "fgas@asfasaf.hu",
        "fromname": "dfgvdsfg@dfsgdfg.hu",
        "subject": "dsfgdsfg",
        "email_category": "0",
        "filter": "0",
        "exclude_filter": 0,
        "contactlist": "34135497",
        "exclude_contactlist": null,
        "additional_linktracking_parameters": "",
        "cc_list": 0,
        "html_source": "<html><head></head><body>dsfgdsfgsdf</body></html>",
        "text_source": "",
        "template": "0",
        "unsubscribe": "y",
        "browse": "y",
        "status": "3",
        "api_status": "2",
        "api_error": "0",
        "external_event_id": null,
        "combined_segment_id": null,
        "value_control": null,
        "recurring": "n",
        "type": "1",
        "keep_raw_html": 0,
        "keep_raw_text": 0,
        "content_type": "html",
        "text_only": "n",
        "source": "userlist"
    }',

    '/internal/{customerId}/email/' => '[
        {
          "id": "2",
          "administrator": "3",
          "event": "9",
          "language": "en",
          "name": "Copy of Copy of Copy of Empty Test",
          "created": "2016-10-28 13:39:26",
          "deleted": "",
          "fromemail": "test@test.hu",
          "fromname": "Daniel",
          "subject": "asd",
          "email_category": "0",
          "filter": "0",
          "exclude_filter": 0,
          "contactlist": "0",
          "exclude_contactlist": 0,
          "template": "0",
          "unsubscribe": "y",
          "browse": "y",
          "text_only": "n",
          "status": "1",
          "api_status": "0",
          "api_error": 0,
          "source": "userlist"
        },
        {
          "id": "3",
          "administrator": "3",
          "event": "0",
          "language": "en",
          "name": "Copy of Copy of Empty Test",
          "created": "2016-10-28 13:38:48",
          "deleted": "",
          "fromemail": "test@test.hu",
          "fromname": "Daniel",
          "subject": "asd",
          "email_category": "0",
          "filter": "1",
          "exclude_filter": 0,
          "contactlist": "0",
          "exclude_contactlist": null,
          "template": "0",
          "unsubscribe": "y",
          "browse": "y",
          "text_only": "n",
          "status": "3",
          "api_status": "0",
          "api_error": 0,
          "source": "profile"
        }
    ]',
    '/internal/{customerId}/administrator/' => '[
        {
            "id": "1",
            "username": "admin",
            "email": "",
            "first_name": "",
            "last_name": "",
            "interface_language": "en",
            "default_upages_lang": "en",
            "access_level": "0",
            "position": "",
            "title": "0",
            "tz": "",
            "mobile_phone": "",
            "may_delete": "n",
            "last_invitation_action_date": "",
            "pwd_update_interval": "90",
            "two_fa_auth_enabled": 0,
            "mobile_phone_verified": 0,
            "distance_unit": "km"
        },
        {
            "id": "2",
            "username": "admin2",
            "email": "",
            "first_name": "",
            "last_name": "",
            "interface_language": "en",
            "default_upages_lang": "en",
            "access_level": "0",
            "position": "",
            "title": "0",
            "tz": "",
            "mobile_phone": "",
            "may_delete": "n",
            "last_invitation_action_date": "",
            "pwd_update_interval": "90",
            "two_fa_auth_enabled": 0,
            "mobile_phone_verified": 0,
            "distance_unit": "km"
        }
    ]',

    '/internal/{customerId}/event' => '[
        {
            "id": "1",
            "name": "event egy",
            "usages": {
                "program_ids": [],
                "email_ids": ["3"]
            }
        },
        {
            "id": "2",
            "name": "event ketto",
            "usages": {
                "program_ids": ["4"],
                "email_ids": []
            }
        }
    ]',

    '/internal/{customerId}/filter' => '[
        {
            "id": "1",
            "name": "segment 1",
            "type": "standard",
            "predefinedSegmentId": "3"
        },
        {
            "id": "2",
            "name": "segment 2",
            "type": "standard",
            "predefinedSegmentId": "5"
        }
    ]',
];
