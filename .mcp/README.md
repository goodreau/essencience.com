# Essencience.com MCP Server

Model Context Protocol server for Laravel and Git operations.

## Setup

```bash
cd .mcp
npm install
npm run build
```

## Available Tools

### Git Operations
- `git_status` - Get current repository status
- `git_log` - View commit history
- `git_diff` - Show changes (staged/unstaged)
- `git_commit` - Commit staged changes
- `git_add` - Stage files for commit

### Laravel Operations
- `artisan` - Run any artisan command
- `artisan_list` - List all available artisan commands
- `composer` - Run composer commands

## Configuration

Add to your MCP settings (e.g., Claude Desktop config):

```json
{
  "mcpServers": {
    "essencience-laravel": {
      "command": "node",
      "args": ["/Volumes/EXTERNAL/Essencience.com/.mcp/dist/index.js"],
      "cwd": "/Volumes/EXTERNAL/Essencience.com/.mcp"
    }
  }
}
```

## Usage Examples

**Run migrations:**
```json
{
  "tool": "artisan",
  "arguments": {
    "command": "migrate"
  }
}
```

**Check git status:**
```json
{
  "tool": "git_status"
}
```

**Install Livewire:**
```json
{
  "tool": "composer",
  "arguments": {
    "command": "require livewire/livewire"
  }
}
```
