# Destever structure sync plan

## DB managed content

- Posts and page bodies
- Media uploads
- Comments
- User-generated content

## Structure that should be recreated from code

1. The `Blog` submenu must always include:
   - `전체`
   - `잡상노트`
   - `일상`
   - `캐나다 워홀`
   - `Done List`
2. Critical page URLs such as `/oc-couples/` must keep their intended template relationship.
3. Category slugs referenced by code must exist and stay stable.

## Immediate implementation

- Add a mu-plugin that repairs critical menu children from code.
- Extend deployment so `wp-content/mu-plugins` is synced together with the child theme.
- Use Git as the source of truth for theme code plus structure-repair code.

## Next recommended step

- Add a second structure sync pass for critical page-template bindings and required categories.
