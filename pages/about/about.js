/*
 * 
 * 织梦版微信小程序
 * author: 鹏厄
 * 小镇故事66UI.com

 * 
 */
var Api = require('../../utils/api.js');
var util = require('../../utils/util.js');
var WxParse = require('../../wxParse/wxParse.js');
var wxApi = require('../../utils/wxApi.js')
var wxRequest = require('../../utils/wxRequest.js')
var Auth = require('../../utils/auth.js');
import config from '../../utils/config.js'
var app = getApp();Page({
  data: {
    title: '页面内容',
    pageData: {},
    pagesList: {},
    display: 'none',
    wxParseData: [],
    praiseList: [],
    dialog: {
      title: '',
      content: '',
      hidden: true
    },
    userInfo: {},
    isLoginPopup: false,
    openid: "",
    system: ""
  },
  onLoad: function(options) {
    var self = this;
    wx.setNavigationBarTitle({
      title: '关于小镇故事 Life',
      success: function(res) {
        // success
      }
    });
    Auth.setUserInfoData(self);
    Auth.checkLogin(self);
   
    wx.getSystemInfo({
      success: function(t) {
        var system = t.system.indexOf('iOS') != -1 ? 'iOS' : 'Android';
        self.setData({
          system: system
        });
      }
    })
  },
  praise: function() {
    var self = this;
    var minAppType = config.getMinAppType;
    var system = self.data.system;
    if (minAppType == "0" && system == 'Android') {
      if (self.data.openid) {
        wx.navigateTo({
          url: '../pay/pay?flag=2&openid=' + self.data.openid + '&postid=' + config.getAboutId
        })
      } else {
        Auth.checkSession(self, 'isLoginNow');
      }
    } else {
      var src = config.getZanImageUrl;
      wx.previewImage({
        urls: [src],
      });
    }
  },
  onPullDownRefresh: function() {
    var self = this;
    self.setData({
      display: 'none',
      pageData: {},
      wxParseData: {},
    });
 
    //消除下刷新出现空白矩形的问题。
    wx.stopPullDownRefresh()
  },
  onShareAppMessage: function() {
    return {
      title: '关于“' + config.getWebsiteName + '”官方小程序',
      path: 'pages/about/about',
      success: function(res) {
        // 转发成功
      },
      fail: function(res) {
        // 转发失败
      }
    }
  },
  gotowebpage: function() {
    var self = this;
    var minAppType = config.getMinAppType;
    var url = '';
    if (minAppType == "0") {
      url = '../webpage/webpage?';
      wx.navigateTo({
        url: url
      })
    } else {
      self.copyLink(config.getDomain);
    }
  },
  copyLink: function(url) {
    //this.ShowHideMenu();
    wx.setClipboardData({
      data: url,
      success: function(res) {
        wx.getClipboardData({
          success: function(res) {
            wx.showToast({
              title: '链接已复制',
              image: '../../images/link.png',
              duration: 2000
            })
          }
        })
      }
    })
  },
  //给a标签添加跳转和复制链接事件
  wxParseTagATap: function(e) {
    var self = this;
    var href = e.currentTarget.dataset.src;
    console.log(href);
    var domain = config.getDomain;
    //我们可以在这里进行一些路由处理
    if (href.indexOf(domain) == -1) {
      wx.setClipboardData({
        data: href,
        success: function(res) {
          wx.getClipboardData({
            success: function(res) {
              wx.showToast({
                title: '链接已复制',
                //icon: 'success',
                image: '../../images/link.png',
                duration: 2000
              })
            }
          })
        }
      })
    } else {
      var slug = util.GetUrlFileName(href, domain);
      if (slug == 'index') {
        wx.switchTab({
          url: '../index/index'
        })
      } else {
        var getPostSlugRequest = wxRequest.getRequest(Api.getPostBySlug(slug));
        getPostSlugRequest
          .then(res => {
            var postID = res.data[0].id;
            var openLinkCount = wx.getStorageSync('openLinkCount') || 0;
            if (openLinkCount > 4) {
              wx.redirectTo({
                url: '../detail/detail?id=' + postID
              })
            } else {
              wx.navigateTo({
                url: '../detail/detail?id=' + postID
              })
              openLinkCount++;
              wx.setStorageSync('openLinkCount', openLinkCount);
            }
          })
      }
    }
  },
  agreeGetUser: function(e) {
    var userInfo = e.detail.userInfo;
    var self = this;
    if (userInfo) {
      auth.getUsreInfo(e.detail);
      self.setData({
        userInfo: userInfo
      });
    }
    setTimeout(function() {
      self.setData({
        isLoginPopup: false
      })
    }, 1200);
  },
  closeLoginPopup() {
    this.setData({
      isLoginPopup: false
    });
  },
  openLoginPopup() {
    this.setData({
      isLoginPopup: true
    });
  }
})