
let refOffset = 0;
let visible = true;
const bannerHeight = 87;
let newOffset=0;
const bannerWrapper = select("header");
const banner =select(".main-navbar");
const body=select("body");

function handleStickyNavbar(){
  if (newOffset > bannerHeight) {
    if (newOffset > refOffset) {
      banner.classList.add("sticky");
      banner.classList.remove("animateIn");
      banner.classList.add("animateOut");
    } else {
      banner.classList.add("sticky");
      banner.classList.remove("animateOut");
      banner.classList.add("animateIn");
      // if(newOffset<refOffset){
      //   setTimeout(function(){
      //     banner.classList.remove("animateIn");
      //     banner.classList.add("animateOut");
      //   },5000);
      // }
    }
    banner.style.background = '#071e3d';
    refOffset = newOffset;
  }
  else{
    // banner.style.transition="";
    banner.classList.remove("sticky");
    banner.classList.remove("animateIn");
    banner.classList.remove("animateOut");
  }
  
}


const menuHandler = function(e) {
  newOffset = e.currentTarget.scrollTop;
  handleStickyNavbar();
};

function convertToPersianDate(dateString){
  const timestamp=Date.parse(dateString);
  const dateObj=new persianDate(timestamp).ON.persianAstro;
  const monthNames=['فروردین',"اردیبهشت","خرداد","تیر","مرداد","شهریور","مهر","آبان","آذر","دی","بهمن","اسفند"];
  return dateObj.day+" "+monthNames[dateObj.month]+" "+dateObj.year;
}
function showPost(post) {
  let output = "<div class='post'>";


  output += "<img class='post__thumbnail' src='" + post.thumbnail.source_url + "'/>";

  output += "<div class='post__body '><h5 class='post__title'>" + post.title.rendered + "</h5>";


  output += "<ul class='post__categories'>";
  post.post_categories.forEach(function(category){
      output += "<li class='post__category'><a href='" + category.link + "'>#" + category.name + "</a></li>";
  });
  output += "</ul>";

  output += "<p class='post__content'>" + post.excerpt.rendered + "</p>";
  output += "<div class='post__footer'>" + "<a href='" + post.guid.rendered + "' class='button read--more is--outline'>ادامه خواندن</a></div>";
  output += "</div></div>";
  return output;

}
function showPostsInFooter(posts){
  let output="";
  posts.forEach(function(post){
      output+="<li><a href='"+post.guid.rendered+"'><article class='post'><div class='post__thumbnail'><img src='"+post.thumbnail.source_url+"'></div><div class='post__details'><div class='post__details__title'>"+post.title.rendered+"</div><div class='post__details__date'><i class='icon-clock'></i>"+convertToPersianDate(post.date)+"</div></div></article></a></li>";
  });
  select("#posts-wrap-footer").innerHTML=output;
}
function loadWpPosts() {
  axios.get("http://www.motarjem1.com/blog/wp-json/wp/v2/posts?per_page=3&page=1")
      .then(function (res) {
          let postDetails = res.data;
          postDetails.forEach(function (post, index) {
              axios.get(post["_links"]['wp:featuredmedia'][0]['href'])
                  .then(function (res) {
                      postDetails[index]["thumbnail"] = res.data["media_details"]["sizes"]["medium_thumb"];
                      console.log(postDetails);
                        setTimeOut(function(){
                               axios.get(post["_links"]["wp:term"][0]["href"])
                                .then(function (res) {
                                    links = [];
                                    let data = res.data;
                                    for (let i = 0; i < data.length; i++) {
                                        links.push({
                                        "link": data[i].link,
                                        "name": data[i].name
                                        });
                                    }
                                  postDetails[index]['post_categories'] = links;
                                  if(window.location.pathname=="/"){
                                    let posts = "";
                                    postDetails.forEach(function (post) {
                                        posts += "<div class='col-lg-4'>";
                                        posts += showPost(post);
                                        posts += "</div>";
                                    });
                                    select("#posts-wrap").innerHTML=posts;
                                    select(".visit-blog").style.display="flex";
                                  }
                                  showPostsInFooter(postDetails);
                            });
                        },500);
                  });
             
          });
         
      });
}

document.addEventListener("DOMContentLoaded", function(e) {
  
  //navbar toggle
  const navToggle = select(".nav-toggle");
  const navbar = select(".nav-menu");
  addListener(navToggle, "click", function(e) {
    e.currentTarget.classList.toggle("active");
    navbar.classList.toggle("active");
  });
});
//sticky navigation scroll animation
  
body.addEventListener("scroll",menuHandler, false);

// create slider indicators dynamically

// document.addEventListener("DOMContentLoaded", function(e) {
//   const mainSlider = select("#main-slider");
//   const slidesCount = selectAll(".carousel-item").length;
//   //create indicator wrapper
//   const ol = createEl("ol", ["carousel-indicators"]);
//   let li=createEl("li",['active'],{"data-target":"#main-slider","data-slide-to":"0"});
//   ol.appendChild(li);
//   //create indicators
//   for (let i=1;i<slidesCount;i++){
//     let li=createEl("li",[],{"data-target":"#main-slider","data-slide-to":i});
//     ol.appendChild(li);
//   }

//   //append indicators to slider wrapper
//   mainSlider.prepend(ol);
// });
