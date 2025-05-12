# Directory Tree

> Elegantly transform directory structures into traversable tree objects

![License](https://img.shields.io/github/license/hizpark/directory-tree?style=flat-square)
![Latest Version](https://img.shields.io/packagist/v/hizpark/directory-tree?style=flat-square)
![PHP Version](https://img.shields.io/badge/php-8.2--8.4-blue?style=flat-square)
![Static Analysis](https://img.shields.io/badge/static_analysis-PHPStan-blue?style=flat-square)
![Tests](https://img.shields.io/badge/tests-PHPUnit-brightgreen?style=flat-square)
[![codecov](https://codecov.io/gh/hizpark/directory-tree/branch/main/graph/badge.svg)](https://codecov.io/gh/hizpark/directory-tree)
![CI](https://github.com/hizpark/directory-tree/actions/workflows/ci.yml/badge.svg?style=flat-square)

This library provides object-oriented directory structure mapping and transformation capabilities for PHP applications. It converts physical filesystem hierarchies into traversable tree objects with support for multiple export formats including JSON, XML and visual representations.

## ✨ 特性

- 将实体目录结构映射为记忆中的树形结构
- 支持祖先、父代、子代、兄弟节点的查询
- 提供 Array JSON XML 多种格式输出
- 支持树形文本、缩进文本 HTML/UL 等展示格式

## 📦 安装

```bash
composer require hizpark/directory-tree
```

## 📂 目录结构

```txt
src
├── Builder
│   ├── DirectoryTreeBuilder.php
│   └── TreeBuilderInterface.php
├── Exception
│   └── DirectoryTreeException.php
├── Memory
│   ├── DirectoryTreeMemory.php
│   └── TreeMemoryInterface.php
├── Node
│   ├── DirectoryNode.php
│   └── NodeInterface.php
├── Transformer
│   └── DirectoryTreeTransformer.php
└── Viewer
    └── DirectoryTreeViewer.php
```

## 🚀 使用示例

### 示例1：基本目录树渲染

```php
use Hizpark\DirectoryTree\Viewer\DirectoryTreeViewer;

$viewer = new DirectoryTreeViewer();
echo $viewer->render('/path/to/directory');
```

### 示例2：多种格式输出

```php
use Hizpark\DirectoryTree\Viewer\DirectoryTreeViewer;

$viewer = new DirectoryTreeViewer();

// HTML列表格式
echo $viewer->render('/path/to/directory', DirectoryTreeViewer::FORMAT_HTML_LIST);
// Markdown列表格式
echo $viewer->render('/path/to/directory', DirectoryTreeViewer::FORMAT_MARKDOWN_LIST);
// 文本缩进格式
echo $viewer->render('/path/to/directory', DirectoryTreeViewer::FORMAT_TEXT_INDENTED);
// 树状文本格式(默认)
echo $viewer->render('/path/to/directory', DirectoryTreeViewer::FORMAT_TEXT_TREE);
```

## 📐 接口说明

### NodeInterface

> 定义目录树节点的基本接口

```php
namespace Hizpark\DirectoryTree\Node;

interface NodeInterface
{
    public function getPath(): string;
    public function getLocation(): string;
    public function getParent(): ?NodeInterface;
    public function addChild(NodeInterface $child): void;
    public function getChildren(): ?array;
}
```

### TreeBuilderInterface

> 定义构建目录树结构的接口

```php
namespace Hizpark\DirectoryTree\Builder;

use Hizpark\DirectoryTree\Memory\TreeMemoryInterface;
use Hizpark\DirectoryTree\Node\NodeInterface;

interface TreeBuilderInterface
{
    public function build(NodeInterface $root): TreeMemoryInterface;
}
```

### TreeMemoryInterface

> 定义目录树内存操作的接口

```php
namespace Hizpark\DirectoryTree\Memory;

use Hizpark\DirectoryTree\Node\NodeInterface;

interface TreeMemoryInterface
{
    public function getRoot(): NodeInterface;
    public function getAncestors(NodeInterface $node): array;
    public function getSiblings(NodeInterface $node): array;
    public function getDescendants(NodeInterface $node): array;
}
```

## 🛠️ 核心类说明

### DirectoryNode

> Namespace: `Hizpark\DirectoryTree\Node`

- 实现 NodeInterface
- 代表目录树中的单个节点
- 包含路径、父节点、子节点等信息
- 自动验证路径有效性

### DirectoryTreeBuilder

> Namespace: `Hizpark\DirectoryTree\Builder`

- 实现 TreeBuilderInterface
- 使用迭代方式构建目录树
- 自动排序文件和目录
- 支持大目录处理

### DirectoryTreeMemory

> Namespace: `Hizpark\DirectoryTree\Memory`

- 实现 TreeMemoryInterface
- 提供树结构查询功能：获取祖先节点，获取兄弟节点，获取后代节点
- 使用BFS算法遍历

### DirectoryTreeTransformer

> Namespace: `Hizpark\DirectoryTree\Transformer`

- 目录树结构转换器
- 支持多种输出格式转换： 数组，JSON，XML，HTML列表，Markdown列表，缩进文本，ASCII树状文本
- 保持原始目录结构
- 自动处理特殊字符转义

### DirectoryTreeViewer

> Namespace: `Hizpark\DirectoryTree\Viewer`

- 提供简化的渲染接口
- 支持多种输出格式：HTML列表，Markdown列表，文本缩进，树状文本
- 内置异常处理

`DirectoryTreeViewer::render(string $path, int $format = self::FORMAT_TEXT_TREE)`

#### `$format` 可选值

| 常量名                                       | 值 | 描述            |
|-------------------------------------------|---|---------------|
| DirectoryTreeViewer::FORMAT_TEXT_TREE     | 1 | 树状文本格式        |
| DirectoryTreeViewer::FORMAT_TEXT_INDENTED | 2 | 缩进文本格式        |
| DirectoryTreeViewer::FORMAT_MARKDOWN_LIST | 3 | Markdown 列表格式 |
| DirectoryTreeViewer::FORMAT_HTML_LIST     | 4 | HTML 列表格式     |

> 默认值： `DirectoryTreeViewer::FORMAT_TEXT_TREE`

## 🔍 静态分析

使用 PHPStan 工具进行静态分析，确保代码的质量和一致性：

```bash
composer stan
```

## 🎯 代码风格

使用 PHP-CS-Fixer 工具检查代码风格：

```bash
composer cs:chk
```

使用 PHP-CS-Fixer 工具自动修复代码风格问题：

```bash
composer cs:fix
```

## ✅ 单元测试

执行 PHPUnit 单元测试：

```bash
composer test
```

执行 PHPUnit 单元测试并生成代码覆盖率报告：

```bash
composer test:coverage
```

## 🤝 贡献指南

欢迎 Issue 与 PR，建议遵循以下流程：

1. Fork 仓库
2. 创建新分支进行开发
3. 提交 PR 前请确保测试通过、风格一致
4. 提交详细描述

## 📜 License

MIT License. See the [LICENSE](LICENSE) file for details.
