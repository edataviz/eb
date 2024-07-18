<div id="chatUserFuncMenu">
    <ul>
        <li><i class="icon_add"></i>Add to favourite</li>
        <li><i class="icon_block"></i>Block this user</li>
        <li><i class="icon_delete"></i>Delete chat history</li>
    </ul>
</div>
<div id="chatTooltip">
    <span class="chatTooltipText"></span>
    <span class="chatTooltipArrow">&#x25BA;</span>
</div>
<div id="chatBoxLeft">
    <div id="chatBoxSearch">
        <input id="chatSearchInput" placeholder="Search user">
        <hr>
    </div>
    <div id="chatFriendsList">
        <div class="chatUser">
            <div class="avatar">NT</div>
            <div class="chatMsgCount">99</div>
            <div class="chatName">Nguyen Tung</div>
            <div class="chatText">Last online at 10:00</div>
        </div>
    </div>
</div>
<div id="chatBoxRight">
    <div id="chatHeader">
        <div class="chatUser">
            <div class="avatar">NT</div>
            <div class="chatName">Nguyen Tung</div>
            <div class="chatText">Offline</div>
        </div>
    </div>
    <div id="chatDetail">
        chatDetail
    </div>
    <div id="chatEdit">
        <textarea contenteditable id="chatMessageInput" placeholder="Type a message here"></textarea>
        <button id="chatButtonSend" onclick="sendChatMessage()">Send</button>
    </div>
</div>