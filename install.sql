create table achievments.achievments (
    achievment_id integer not null primary key auto_increment,
    achievment_name text,
    achievment_group text,
    execute_command text
);

create table achievments.candidates (
    candidate_id integer not null primary key auto_increment,
    candidate_email text,
    candidate_team text,
    candidate_groups text,
    candidate_salutation text
);

create table achievments.unlocked_achievments (
  unlocked_achievment_id integer not null primary key auto_increment,
  id_achievment integer,
  id_candidate integer,
  unlock_date timestamp
)
