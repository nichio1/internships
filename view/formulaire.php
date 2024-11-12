<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Application Form</title>
</head>
<body>
    <h1>Internship Application Form</h1>

    <form action="#" method="post">
        <h2>Experience and Motivation</h2>

        <label for="interest">Why are you interested in this internship program?</label><br>
        <textarea id="interest" name="interest" rows="4" cols="50" placeholder="Write your answer here..."></textarea><br><br>

        <label for="experience">What previous volunteer or internship experience do you have (if any)?</label><br>
        <textarea id="experience" name="experience" rows="4" cols="50" placeholder="Write your answer here..."></textarea><br><br>

        <label for="skills">Describe any relevant skills (e.g., language proficiency, technical skills):</label><br>
        <textarea id="skills" name="skills" rows="4" cols="50" placeholder="Write your answer here..."></textarea><br><br>

        <label for="goals">What are your personal goals for this internship?</label><br>
        <textarea id="goals" name="goals" rows="4" cols="50" placeholder="Write your answer here..."></textarea><br><br>

        <h2>Program Preferences</h2>

        <label for="country">Preferred country for the internship:</label><br>
        <select id="country" name="country">
            <option value="">Select a country</option>
            <option value="USA">USA</option>
            <option value="Canada">Canada</option>
            <option value="Germany">Germany</option>
            <option value="France">France</option>
            <option value="Other">Other</option>
        </select><br><br>

        <label for="other_country">If other, please specify:</label><br>
        <input type="text" id="other_country" name="other_country" placeholder="Enter preferred country"><br><br>

        <label>Preferred duration of the internship:</label><br>
        <input type="radio" id="1-3_months" name="duration" value="1-3 months">
        <label for="1-3_months">1-3 months</label><br>

        <input type="radio" id="3-6_months" name="duration" value="3-6 months">
        <label for="3-6_months">3-6 months</label><br>

        <input type="radio" id="other_duration" name="duration" value="Other">
        <label for="other_duration">Other:</label>
        <input type="text" id="other_duration_text" name="other_duration_text" placeholder="Specify duration"><br><br>

        <label>Type of work or field you’re most interested in:</label><br>
        <input type="checkbox" id="education" name="field" value="Education">
        <label for="education">Education</label><br>

        <input type="checkbox" id="marketing" name="field" value="Marketing">
        <label for="marketing">Marketing</label><br>

        <input type="checkbox" id="it" name="field" value="IT">
        <label for="it">IT</label><br>

        <input type="checkbox" id="healthcare" name="field" value="Healthcare">
        <label for="healthcare">Healthcare</label><br>

        <input type="checkbox" id="social_work" name="field" value="Social Work">
        <label for="social_work">Social Work</label><br>

        <label for="other_field">Other:</label>
        <input type="text" id="other_field" name="other_field" placeholder="Specify other field"><br><br>

        <button type="submit">Submit Application</button>
    </form>
</body>
</html>
