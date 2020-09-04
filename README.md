To run this do composer update

then cd to projectDIR/public

then type the following

php index.php users fetchusers

for running the query this is the console command

php index.php users query "age" "20"

or 

php index.php users query "age" "20" false

Each space is a new parameter so we have field value exact. 

Exact is referenced as a string as that is what is being passed a string value.

To run the report

php index.php users report


SQL statment

I am not sure if I have used the wrong column for the social_score this could be changed.

SELECT distinct companies.`name` as "Company",branches.`name` as "Branch", DATE_FORMAT(survey_responses.visit_datetime,'%m-%y') as "Month", 
				  (select distinct sum(sr.social_score) from survey_responses as sr where sr.company_id = companies.id and sr.branch_id = branches.id and sr.social_type_id=1 and sr.visit_datetime = survey_responses.visit_datetime) as "TripAdvisor Reviews",
                  (select distinct avg(sr.social_score) from survey_responses as sr where sr.company_id = companies.id and sr.branch_id = branches.id and sr.social_type_id=1 and sr.visit_datetime = survey_responses.visit_datetime) as "TripAdvisor Rating",
                  (select distinct sum(sr.social_score) from survey_responses as sr where sr.company_id = companies.id and sr.branch_id = branches.id and sr.social_type_id=2 and sr.visit_datetime = survey_responses.visit_datetime) as "Facebook Reviews",
                  (select distinct avg(sr.social_score) from survey_responses as sr where sr.company_id = companies.id and sr.branch_id = branches.id and sr.social_type_id=2 and sr.visit_datetime = survey_responses.visit_datetime) as "Facebook Rating",
                  (select distinct sum(sr.social_score) from survey_responses as sr where sr.company_id = companies.id and sr.branch_id = branches.id and sr.social_type_id=3 and sr.visit_datetime = survey_responses.visit_datetime) as "Google Reviews",
                  (select distinct avg(sr.social_score) from survey_responses as sr where sr.company_id = companies.id and sr.branch_id = branches.id and sr.social_type_id=3 and sr.visit_datetime = survey_responses.visit_datetime ) as "Google Rating",
                  (select distinct sum(sr.social_score) from survey_responses as sr where sr.company_id = companies.id and sr.visit_datetime = survey_responses.visit_datetime) as "Total Reviews",
                  (select distinct avg(sr.social_score) from survey_responses as sr where sr.company_id = companies.id and sr.visit_datetime = survey_responses.visit_datetime) as "Average Rating"
                   FROM survey_responses 
                  left join branches on survey_responses.branch_id = branches.id 
                  left join companies on companies.id = branches.company_id where branches.`status` in(1,2,5) and branches.`brand_site` = 0 and survey_mode_id = 8  ORDER By `Company`,`Month`;
                  



