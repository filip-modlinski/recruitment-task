# PHP developer recruitment task
Aim of this task is to order a package label using our API with provided data, also to process the response and to get information from tracking using and process the response according to TO DO section (2).


*Code should follow SOLID principles, but not over complicated, be readable.*


*Feel free to use libraries.*
​
​
## TO DO
### 1) Write a PHP CLI script which will do the following:
1. Reads data from two csv files. Can be found in `/source` folder.
2. Fires requests to Świat Przesyłek API to create couriers for provided lines. One request for each line. 
3. Retrieves labels (PNG) and package ids, saves labels it in `/labels` directory with random name and all package ids in one file `package_ids.txt` files in the format: `PACKAGE_ID:LABEL_NAME`.
4. After retrieving labels all labels should be merged in one pdf file in root folder. 
​
​
### 2) Write a PHP CLI script which will do the following:
1. Reads statuses from test tracking endpoint.
2. In case if date of last status is not older than 12 hours (`date` field), you should emulate email sending (save last status as one line into file called `emails.txt` in format: `package id`;`status map name`;`date`).
3. In case if date of last status is not older than 12 hours (`date` field), it is `DELIVERED` and there is daytime in destination country (`country_to` field)*, you should emulate sms sending (save  `package id`;`status map name`;`date` to file called `sms.txt`).
​
Notes:
In `stat_map_history` you will find history of status maps. 
Dictionary for ids you can find in API doc.
For time zone use country's capital time zone.
There are 3 possible receiver countries: DE, PT, US. But nice if script could work for any country.
​
## Requirements
- Composer & PSR4 autoload.
​
## Nice-to-have
- Guzzle for requests, all requests/responses should be logged in additional log file.
​
## References:
Source files: 
- first file contains address data of sender & receiver
- second — weight & dimensions of a package
​
Each line of dimensions file should match the same line of address data file.
API documentation is in `source` folder. Use this method: `courier/create-pre-routing` with the following credentials:
```
LOGIN: <provided_in_the_email>
API KEY: <provided_in_the_email>
Environment: production
Method to use in 1: courier/create-pre-routing
Method to use in 2: track/test (not present in API doc)
```
## Checklist
- Labels should be saved in folder `/labels/<Today_date_in_DmY_format>/` and be rotated 90 degrees clockwise
- `package_ids.txt` should be in the root folder 
- `merged_labels.pdf` should be in the root folder
- log files `sms.txt` & `emails.txt` should be in the root folder
- `courier/create-pre-routing` is limited to 1 request per 5 seconds. You should find a way how to deal with it
​
## Submitting
The result is accepted as a *pull request in the fork* of this repository.
​

*Good luck! If you have any questions feel free to ping us!*
