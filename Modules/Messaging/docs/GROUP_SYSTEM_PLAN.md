# Group Messaging System - Implementation Plan

## 1. Database Changes

### New Migration - Add Group Fields
```php
// Add to conversations table
- name (string, nullable) - Group name
- avatar (string, nullable) - Group avatar
- admin_id (bigInteger, nullable) - Who created/manages group
- is_group (boolean, default false) - Is this a group conversation
- created_at, updated_at already exist
```

## 2. Model Updates

### Conversation Model (update)
```php
// conversation_type: 'direct' | 'group'
// is_group: boolean

// Add scopes:
// scopeGroup($query)
// scopeDirect($query)

// Add methods:
// - isGroup()
// - isAdmin($userId, $userType)
// - getGroupAdmins()
```

### ConversationParticipant Model (update)
```php
// Add fields:
// - role (string: 'member'|'admin'|'moderator')
// - joined_at
// - left_at (for left members)

// Add methods:
// - isAdmin()
// - isModerator()
// - canManageMessages()
```

## 3. API/Controller Changes

### ChatController - Add new endpoints
```
GET    /admin/groups              - List all groups
POST   /admin/groups              - Create new group
GET    /admin/groups/{id}         - View group details
PUT    /admin/groups/{id}         - Update group
DELETE /admin/groups/{id}         - Delete group
POST   /admin/groups/{id}/members - Add members
DELETE /admin/groups/{id}/members/{userId} - Remove member
POST   /admin/groups/{id}/make-admin - Make user admin
```

## 4. Frontend Changes

### Admin Dashboard (admin.blade.php)
```
1. Add "Groups" tab in sidebar
2. Create group list view
3. Create group chat view
4. Add group creation modal
5. Add member management UI
```

### Key UI Components:
- Group list with avatar, name, member count
- Group info panel (members list, group info)
- Group settings (admin only)
- Member add/remove functionality

## 5. Implementation Order

1. **Step 1**: Run migration to add group fields
2. **Step 2**: Update Conversation model
3. **Step 3**: Update ConversationParticipant model  
4. **Step 4**: Add routes for group management
5. **Step 5**: Create/update controllers
6. **Step 6**: Update frontend views
7. **Step 7**: Test functionality

## 6. Key Considerations

- Group name max 100 chars
- Max members per group: configurable (default 50)
- Only group admin can: rename, add/remove members, make admins
- Members can leave group
- When admin leaves, assign new admin or archive group
- Direct messages converted to group not supported (keep separate)