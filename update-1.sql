alter table achievments.achievments add column description text;

create table achievments.templates (
  id integer not null primary key auto_increment,
  candidate_team text,
  template text
);

alter table achievments.candidates add column workhour_start time;
alter table achievments.candidates add column workhour_end time;
