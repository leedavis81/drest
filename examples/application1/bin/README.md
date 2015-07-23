To generate the database required to set up this example simply use the doctrine cli tools.
They should be located in/vendor/bin/doctrine.php

Ensure the credentials in cli-config are OK for setting up the test database on, and that the DB has been created. Then run:

../../../vendor/bin/doctrine.php orm:schema-tool:create

