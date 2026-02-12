// ===== THEME TOGGLE =====
function toggleTheme(){
    document.body.classList.toggle("dark");

    if(document.body.classList.contains("dark")){
        localStorage.setItem("theme","dark");
    }else{
        localStorage.setItem("theme","light");
    }
}

// load theme
window.onload=function(){
    const saved = localStorage.getItem("theme");
    if(saved==="dark"){
        document.body.classList.add("dark");
    }
}

// ===== SIDEBAR =====
function openProfileSidebar(){
document.getElementById("profileSidebar").classList.add("active");
document.getElementById("profileOverlay").classList.add("active");
}

function closeProfileSidebar(){
document.getElementById("profileSidebar").classList.remove("active");
document.getElementById("profileOverlay").classList.remove("active");
}
