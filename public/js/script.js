window.onload = () => {
  const api_url = document.getElementById("fcbjc_host_url").value;
  const background_active_color = document.getElementById(
    "fcbjc_background_active_color"
  ).value;
  const background = document.getElementById("fcbjc_background");
  const open_bar = document.getElementById("fcbjc_open_bar");
  const bar = document.getElementById("fcbjc_bar");
  const call_button = document.getElementById("fcbjc_call_button");
  const contact_button = document.getElementById("fcbjc_contact_button");
  const chat_button = document.getElementById("fcbjc_chat_button");
  const close_bar_button = document.getElementById("fcbjc_close_bar_button");
  const popup = document.getElementById("fcbjc_popup");
  const lead_container = document.getElementById("fcbjc_lead_container");
  const chat_container = document.getElementById("fcbjc_chat_container");
  const lead_form = document.getElementById("fcbjc_lead_form");
  const lead_success = document.getElementById("fcbjc_success");
  const lead_error = document.getElementById("fcbjc_lead_error");
  const lead_name = document.getElementById("fcbjc_lead_name");
  const lead_email = document.getElementById("fcbjc_lead_email");
  const lead_message = document.getElementById("fcbjc_lead_message");
  const lead_button = document.getElementById("fcbjc_lead_button");
  let button_active = null;

  function fcbjc_open_bar() {
    open_bar.style.bottom = "-50px";
    bar.style.display = "flex";
    setTimeout(() => {
      bar.style.bottom = "20px";
    }, 50);
    setTimeout(() => {
      open_bar.style.display = "none";
    }, 300);
  }

  function fcbjc_close_bar() {
    open_bar.style.display = "flex";
    if (button_active && button_active !== "call") {
      fcbjc_inactive_bar_button(button_active);
      fcbjc_close_popup();
      setTimeout(() => {
        bar.style.bottom = "-60px";
        setTimeout(() => {
          open_bar.style.bottom = "0";
        }, 50);
        setTimeout(() => {
          bar.style.display = "none";
        }, 300);
      }, 300);
      return;
    }
    if (button_active && button_active === "call") {
      fcbjc_inactive_bar_button(button_active);
      button_active = null;
    }
    bar.style.bottom = "-60px";
    setTimeout(() => {
      open_bar.style.bottom = "0";
    }, 50);
    setTimeout(() => {
      bar.style.display = "none";
    }, 300);
  }

  function fcbjc_get_button_by_name(button) {
    if (button === "contact") {
      return contact_button;
    }
    if (button === "chat") {
      return chat_button;
    }
    return call_button;
  }

  function fcbjc_inactive_bar_button(button) {
    const buttonDOM = fcbjc_get_button_by_name(button);
    buttonDOM.style.backgroundColor = "";
    buttonDOM.style.margin = "0";
    buttonDOM.style.borderRadius = "0";
    buttonDOM.style.padding = "8px";
    const svg = buttonDOM.querySelector("svg");
    svg.setAttribute("width", "28");
    svg.setAttribute("height", "28");
    const span = buttonDOM.querySelector("span");
    span.classList.remove("active");
  }

  function fcbjc_active_bar_button(button) {
    if (button_active) {
      fcbjc_inactive_bar_button(button_active);
    }
    button_active = button;
    const buttonDOM = fcbjc_get_button_by_name(button);
    buttonDOM.style.backgroundColor = background_active_color;
    buttonDOM.style.margin = "8px";
    buttonDOM.style.borderRadius = "10px";
    buttonDOM.style.padding = "2px";
    const svg = buttonDOM.querySelector("svg");
    svg.setAttribute("width", "20");
    svg.setAttribute("height", "20");
    const span = buttonDOM.querySelector("span");
    span.classList.add("active");
  }

  function fcbjc_click_bar_button(button) {
    if (button_active && button_active === button && button === "call") {
      return;
    }
    if (button_active && button === "call") {
      fcbjc_inactive_bar_button(button_active);
      fcbjc_close_popup();
      fcbjc_active_bar_button(button);
      return;
    }
    if (button_active && button_active === button) {
      fcbjc_inactive_bar_button(button_active);
      fcbjc_close_popup();
      return;
    }
    if (button === "call") {
      fcbjc_active_bar_button(button);
      return;
    }
    fcbjc_open_popup(button);
  }

  function fcbjc_open_popup(button) {
    background.style.display = "block";
    popup.style.display = "flex";
    if (button_active) {
      fcbjc_active_bar_button(button);
      setTimeout(() => {
        popup.style.maxHeight = "0";
        setTimeout(() => fcbjc_show_container(button), 300);
      }, 50);
      return;
    }
    fcbjc_active_bar_button(button);
    setTimeout(() => fcbjc_show_container(button), 50);
  }

  function fcbjc_show_container(button) {
    if (button === "contact") {
      chat_container.style.display = "none";
      lead_container.style.display = "flex";
    } else if (button === "chat") {
      lead_container.style.display = "none";
      chat_container.style.display = "flex";
    }
    background.style.opacity = "1";
    popup.style.maxHeight = "500px";
  }

  async function fcbjc_send_lead() {
    if (!lead_name.value || !lead_email.value || !lead_message.value) {
      lead_error.style.display = "block";
      return;
    }
    lead_button.classList.add("fcbjc_lead_button_loading");
    lead_error.style.display = "none";
    const data = {
      name: lead_name.value,
      email: lead_email.value,
      message: lead_message.value,
    };
    const response = await fetch(`${api_url}/?rest_route=/fcbjc/v1/lead`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    });
    lead_button.classList.remove("fcbjc_lead_button_loading");
    if (response.ok) {
      lead_form.style.display = "none";
      lead_success.style.display = "block";
      return;
    }
    lead_error.innerHTML = "Something went wrong, please try again";
    lead_error.style.display = "block";
  }

  function fcbjc_close_popup() {
    background.style.opacity = "0";
    popup.style.maxHeight = "0";
    setTimeout(() => {
      background.style.display = "none";
      popup.style.display = "none";
      lead_container.style.display = "none";
      chat_container.style.display = "none";
    }, 300);
    button_active = null;
  }

  background.addEventListener("click", fcbjc_close_popup);
  open_bar.addEventListener("click", fcbjc_open_bar);
  call_button.addEventListener("click", () => fcbjc_click_bar_button("call"));
  contact_button.addEventListener("click", () =>
    fcbjc_click_bar_button("contact")
  );
  chat_button.addEventListener("click", () => fcbjc_click_bar_button("chat"));
  lead_button.addEventListener("click", () => fcbjc_send_lead());
  close_bar_button.addEventListener("click", fcbjc_close_bar);
};
