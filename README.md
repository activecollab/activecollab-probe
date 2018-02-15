# ActiveCollab Probe

Use ``probe.php`` script to check if your system can run [ActiveCollab](https://www.activecollab.com/index.html) or not. 

Instructions:

1. Download latest ``probe.php`` from GitHub, 
1. Open ``probe.php`` using your favorite text editor and set database connection parameters. ``DB_HOST`` is your MySQL hostname, ``DB_USER`` is MySQL account that you will be useding to connect to the server, ``DB_PASS`` is account password, and ``DB_NAME`` is the name of database that you plan to use, 
1. Upload it to the server where you plan to host ActiveCollab, 
1. Visit ``probe.php`` using your browser. Script will run the test and show you the results.

Each test can have one of the three outputs:

1. <span style="color: green">**OK** (green)</span> - requirement is met.
1. <span style="color: orange">**Warning** (orange)</span> - test did not pass, but ActiveCollab does not require that environment option to run. Warnings are usually throw in case of missing extensions that are optional, but good to have, or in cases in early warning about deprecated functionality.
1. <span style="color: red">**Error** (red)</span> - requirement is not met and you will not be able to run ActiveCollab until you reconfigure your environment to support it. Errors are throw in case of missing extensions, or environment settings that will break ActiveCollab (like some unsupported opcode cache extensions).

That's it. If you have any questions or need our assistance, please get in touch: [https://activecollab.com/contact](https://activecollab.com/contact).