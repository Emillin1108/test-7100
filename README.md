# GPX Track Mapper - Mapbox Version

这是一个基于Mapbox的GPX轨迹地图可视化系统，支持用户账户管理和轨迹保存功能。

## 使用说明

1. 将此文件夹放置在XAMPP/htdocs目录下
2. 确保Apache服务器正在运行
3. 访问 http://localhost/final/pages/signin/signin.html 开始使用系统

## 目录结构

- api/: 后端API，处理用户登录和路线数据
- pages/: 前端页面
  - signin/: 登录页面
  - main/: 主页
  - visualization/: 可视化页面
  - admin/: 管理页面
  - lighten/: 点亮路线页面
- data/: 存储路线数据
- js/: JavaScript工具
- css/: 样式表
- assets/: 图像和地图资源

## 注意事项

1. 需要在服务器环境中运行（如XAMPP）
2. 文件权限设置：确保data目录和其子目录可写
3. 上传功能需要有效的PHP环境 