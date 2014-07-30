Feature: Generating RST include files
  In order for my users to have a great documentation experience
  As an SDK contributor
  I want to generate include files based off of service descriptions

  Background:
    Given the ./behatDocTest/src/OpenStack/ObjectStore/Description directory exists
    And the ./behatDocTest/src/OpenStack/Compute/Description directory exists
    And the ./behatDocTest/src/OpenStack/ObjectStore/Description/v2.0.yml file contains:
      """
      catalogName: swift
      catalogType: object-store
      operations:
        PostAccount:
          parameters:
            TempUrl:
              type: string
              location: header
              sentAs: X-Account-Meta-Temp-URL-Key
            TempUrl2:
              type: string
              location: header
              sentAs: X-Account-Meta-Temp-URL-Key-2
            Metadata:
              prefix: X-Account-Meta-
              location: header
        PutContainer:
          parameters:
            Name:
              type: string
              location: uri
              required: true
              filters: [OpenStack\ObjectStore\Description\Filters::isValidContainerName]
            ReadAcl:
              sentAs: X-Container-Read
              type: string
              location: header
            WriteAcl:
              sentAs: X-Container-Write
              type: string
              location: header
            SyncTo:
              sentAs: X-Container-Sync-To
              type: string
              location: header
            VersionsLocation:
              sentAs: X-Versions-Location
              type: string
              location: header
            Metadata:
              prefix: X-Container-Meta-
              location: header
            ContentType:
              sentAs: Content-Type
              location: header
              type: string
            DetectContentType:
              location: header
              type: boolean
              sentAs: X-Detect-Content-Type
            IfNoneMatch:
              location: header
              sentAs: If-None-Match
      """
    And the ./behatDocTest/src/OpenStack/Compute/Description/v2.0.yml file contains:
      """
      catalogName: nova
      catalogType: compute
      operations:
        GetServer:
          parameters:
            Id:
              location: uri
              type: string
      """

  Scenario: Generating files
    When I specify the source directory as ./behatDocTest/src/OpenStack/
    And I specify the destination directory as ./behatDocTest/doc/
    And I generate files
    Then these files should exist:
      | name                                                                  |
      | ./behatDocTest/doc/object-store-v2/_generated/PostAccount.params.rst  |
      | ./behatDocTest/doc/object-store-v2/_generated/PostAccount.sample.rst  |
      | ./behatDocTest/doc/object-store-v2/_generated/PutContainer.params.rst |
      | ./behatDocTest/doc/object-store-v2/_generated/PutContainer.sample.rst |
      | ./behatDocTest/doc/compute-v2/_generated/GetServer.params.rst         |
      | ./behatDocTest/doc/compute-v2/_generated/GetServer.sample.rst         |