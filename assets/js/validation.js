function validateForm() {
    let requiredFields = document.querySelectorAll("[required]");

    if (requiredFields.length === 0) {
        alert("This form has no fields to validate.");
        return false;
    }

    for (let i = 0; i < requiredFields.length; i++) {
        let field = requiredFields[i];

        if (field.type === "radio" || field.type === "checkbox") {
            continue;
        }

        let value = field.value.trim();

        if (value === "") {
            alert("Please fill all required fields.");
            field.focus();
            return false;
        }

        if (field.type === "email") {
            let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailPattern.test(value)) {
                alert("Please enter a valid email address.");
                field.focus();
                return false;
            }
        }
    }

    return true;
}
function autoHideMessages(time = 4000) {

    let messages = document.querySelectorAll(".auto-hide");

    messages.forEach(function(msg){

        setTimeout(function(){

            msg.style.transition = "opacity 0.5s";
            msg.style.opacity = "0";

            setTimeout(function(){
                msg.remove();
            },500);

        },time);

    });

}
document.addEventListener("DOMContentLoaded", function(){
    autoHideMessages();
});