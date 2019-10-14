# WorldCommunityGrid-WCG-API
Script that calls the WCG API to get information about the status of current work units and writes them into a MySQL database. Also displays simple statistics.

# Usage
- Create a new database and create a new table with the structure from MySQLTableStructure.sql
- Change the settings in wcgUpdate.php for your MySQL Server and World Community Account
- Call the script with "php wcgUpdate.php"

# What it does
The Script reads the status of all current work units and writes all available information into the database. If a work unit is already known and some fields have changed, the information is updated. I recommend to call the script at least once every 24 hours, since the history doesn't go back much further.

# Example Statistics

## func_genReport()
By calling func_genReport() you will get information about work units in the queue per device and project ("NextDL" stands for "Next Deadline"):

    Queue:
    DeviceName (ID     ) zika:  7 NextDL:24. 03:32:06
    MCP-KS1    (4950550) zika:  8 NextDL:24. 04:39:11
    MCP-KS10   (5748519) mip1:  2 NextDL:24. 07:38:17
    MCP-KS10   (5748519) zika:135 NextDL:23. 16:20:23
    MCP-RYZEN  (5852886) zika:816 NextDL:21. 16:12:22
    MCP-RYZEN  (5852886) hst1:  8 NextDL:23. 03:18:07
   
The "Pending Validation" block shows all work units, that were uploaded, but have not been validated yet:

    Pending Validation:
    MCP-KS1  (4950550) mcm1:  1 CPU:    23:15:48
    MCP-KS10 (5748519) mcm1:  2 CPU:    05:23:13
    MCP-KS10 (5748519) zika:  5 CPU:    04:07:07
    MCP-RYZEN(5852886) zika: 21 CPU: 01:11:51:16
    

## func_genReportHistory($date);
By calling func_genReportHistory($date) with $date in YYYY-MM-DD will show information about all *validated* work units for the given date:

    History (2019-10-14):
    MCP-4G   (5701633) zika: 10 CPU: 03:03:16:17
    MCP-KS1  (4950550) zika: 10 CPU: 02:13:29:55
    MCP-KS10 (5748519) fahb:  1 CPU:    04:43:43
    MCP-KS10 (5748519) zika: 81 CPU: 02:14:42:44
    MCP-RYZEN(5852886) zika:160 CPU: 10:22:47:57
    
    TOTAL Device:
    MCP-4G   (5701633) WUs: 10 CPU: 03:03:16:17
    MCP-KS1  (4950550) WUs: 10 CPU: 02:13:29:55
    MCP-KS10 (5748519) WUs: 82 CPU: 02:19:26:28
    MCP-RYZEN(5852886) WUs:160 CPU: 10:22:47:57
    
    TOTAL Project:
    zika WUs:261 CPU: 19:06:16:55
    fahb WUs:  1 CPU:    04:43:43
    
    TOTAL Day:
    WUs: 262
    CPU: 19:11:00:38
