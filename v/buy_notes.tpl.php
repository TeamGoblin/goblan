<?php 
global $user;
global $l;
global $notes;
global $courses;
global $professors;
global $terms;
global $years;
global $schools;
?>
<div class="row" style="padding-top: 10px;" id="buy-filter">
	<div class="col-lg-10 col-lg-offset-1">
		<div class="card-form">
			<select name='first' id='first'>
				<option value=''></option>
				<option value='School'>School</option>
				<option value='Course'>Course</option>
				<option value='Professor'>Professor</option>
			</select>
			<select name='school' id='school' class='school'>
				<option value=''></option>
				<?php foreach ($schools as $school) {
					echo '<option value="'.$school->id.'">'.$school->name.'</option>';
				} ?>
			</select>
			<select name='course' id='course' class='course'>
				<option value=''></option>
				<?php foreach ($courses as $course) {
					echo '<option value="'.$course->id.'">'.$course->subject.' '.$course->number.'</option>';
				} ?>
			</select>
			<select name='professor' id='professor' class='professor'>
				<option value=''></option>
				<?php foreach ($professors as $professor) {
					echo '<option value="'.$professor->id.'">'.$professor->lname.', '.$professor->fname.'</option>';
				}?>
			</select>
			<br/>
			<div class="second">
				<select name='second' id='second'>
					<option value=''></option>
					<option value='School' class='schoolz'>School</option>
					<option value='Course' class='coursez'>Course</option>
					<option value='Professor' class='professorz'>Professor</option>
				</select>
				<select name='school2' id='school2' class='school'>
					<option value=''></option>
					<?php foreach ($schools as $school) {
						echo '<option value="'.$school->id.'">'.$school->name.'</option>';
					} ?>
				</select>
				<select name='course2' id='course2' class='course'>
					<option value=''></option>
					<?php foreach ($courses as $course) {
						echo '<option value="'.$course->id.'">'.$course->subject.' '.$course->number.'</option>';
					} ?>
				</select>
				<select name='professor2' id='professor2' class='professor'>
					<option value=''></option>
					<?php foreach ($professors as $professor) {
						echo '<option value="'.$professor->id.'">'.$professor->lname.', '.$professor->fname.'</option>';
					}?>
				</select>
				<br/>
			</div>
			<div class="third">
				<select name='third' id='third'>
					<option value=''></option>
					<option value='School' class='schoolz'>School</option>
					<option value='Course' class='coursez'>Course</option>
					<option value='Professor' class='professorz'>Professor</option>
				</select>
				<select name='school3' id='school3' class='school'>
					<option value=''></option>
					<?php foreach ($schools as $school) {
						echo '<option value="'.$school->id.'">'.$school->name.'</option>';
					} ?>
				</select>
				<select name='course3' id='course3' class='course'>
					<option value=''></option>
					<?php foreach ($courses as $course) {
						echo '<option value="'.$course->id.'">'.$course->subject.' '.$course->number.'</option>';
					} ?>
				</select>
				<select name='professor3' id='professor3' class='professor'>
					<option value=''></option>
					<?php foreach ($professors as $professor) {
						echo '<option value="'.$professor->id.'">'.$professor->lname.', '.$professor->fname.'</option>';
					}?>
				</select>
				<br/>
			</div>
			<select name='time' id='time'>
				<option value="">Term - Year</option>
				<?php foreach ($years as $year) {
					foreach ($terms as $term) {
						echo '<option value="'.$term->term.':'.$year->year.'">'.$term->term . ' '.$year->year.'</option>';
					}
				}
				?>
			</select>
		</div>
	</div>
</div>
<div class='row' style='padding-top: 10px;' id='buying'>
<?php
foreach ($notes[0] as $note) {
	echo "<div class='col-lg-2 col-6'>";
	echo '<a href="/notes/view/'.$note->id.'">';
	echo "<div class='card-form2'>";
	echo '<img src="/'.$note->thumbnail.'" width="100%" style="max-height: 220px"/>';
	echo '<div class="rateit" data-rateit-value="'.$note->avgrating.'" data-rateit-ispreset="true" data-rateit-readonly="true"></div>';
	echo '<div class="note-course">'.$note->subject.' '.$note->number.'</div>';
	echo '<div class="note-professor">'.$note->lname.', '.$note->fname.'</div>';
	echo '<div class="note-year">'.$note->term.' '.$note->year.'</div>';
	echo '<div class="note-title">'.$note->title.'</div>';
	if ($note->currency == 'USD') {
		echo '<div class="note-cost">$'.$note->cost.'</div>';
	} else {
		echo '<div class="note-cost">'.$note->cost.$note->currency.'</div>';
	}
	echo '<div class="note-author">'.$note->alias.'</div>';
	echo "</div>";
	echo "</a>";
	echo "</div>";
}
?>
</div>