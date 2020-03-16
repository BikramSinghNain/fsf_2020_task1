CREATE TABLE roles_marks_data(
    student_id varchar(20) UNIQUE NOT NULL,
    filename char(40) UNIQUE NOT NULL,
    feedback varchar(200) UNIQUE NOT NULL,
    rating int(1) UNIQUE NOT NULL,
    show_filename varchar(200),
    
);
